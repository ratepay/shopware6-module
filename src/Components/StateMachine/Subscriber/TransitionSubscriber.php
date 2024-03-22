<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\StateMachine\Subscriber;

use Exception;
use Psr\Log\LoggerInterface;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentCancelService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentDeliverService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentReturnService;
use Ratepay\RpayPayments\Core\Entity\Extension\OrderExtension;
use Ratepay\RpayPayments\Core\Entity\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Core\PluginConfigService;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryStates;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransitionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $orderDeliveryRepository,
        private readonly EntityRepository $orderRepository,
        private readonly PluginConfigService $configService,
        private readonly PaymentDeliverService $paymentDeliverService,
        private readonly PaymentCancelService $paymentCancelService,
        private readonly PaymentReturnService $paymentReturnService,
        private readonly LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StateMachineTransitionEvent::class => 'onTransition',
        ];
    }

    public function onTransition(StateMachineTransitionEvent $event): void
    {
        if ($event->getEntityName() !== OrderDeliveryDefinition::ENTITY_NAME || !$this->configService->isAutoOperationBasedOnDeliveryStatusEnabled()) {
            return;
        }

        /** @var OrderDeliveryEntity $orderDelivery */
        $orderDelivery = $this->orderDeliveryRepository->search(new Criteria([$event->getEntityId()]), $event->getContext())->first();
        /** @var OrderEntity $order */
        $order = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderDelivery->getOrderId()), $event->getContext())->first();

        if (!MethodHelper::isRatepayOrder($order)) {
            return;
        }

        $ratepayData = $order->getExtension(OrderExtension::EXTENSION_NAME);

        if (!$ratepayData instanceof RatepayOrderDataEntity) {
            $this->logger->warning('Error during bidirectionality: No Ratepay Data was found.', [
                'order' => $order->getId(),
                'orderNumber' => $order->getOrderNumber(),
            ]);
            return;
        }

        switch ($event->getToPlace()->getTechnicalName()) {
            case OrderDeliveryStates::STATE_SHIPPED:
                $operation = OrderOperationData::OPERATION_DELIVER;
                $service = $this->paymentDeliverService;
                break;
            case OrderDeliveryStates::STATE_CANCELLED:
                $operation = OrderOperationData::OPERATION_CANCEL;
                $service = $this->paymentCancelService;
                break;
            case OrderDeliveryStates::STATE_RETURNED:
                $operation = OrderOperationData::OPERATION_RETURN;
                $service = $this->paymentReturnService;
                break;
            default:
                // do nothing
                return;
        }

        // we need this to prevent endless recursion if update-delivery-status is enabled.
        // this should never happen, because shopware can not change the status to the actual status again and so this subscriber should never called again.
        $event->getContext()->addExtension('ratepay', new ArrayStruct([
            'onTransition' => true,
        ]));

        $orderOperationData = new OrderOperationData($event->getContext(), $order, $operation, null, false);
        try {
            $response = $service->doRequest($orderOperationData);
            if (!$response->getResponse()->isSuccessful()) {
                $this->logger->error('Error during bidirectionality. (Exception: ' . $response->getResponse()->getReasonMessage() . ')', [
                    'order' => $order->getId(),
                    'transactionId' => $ratepayData->getTransactionId(),
                    'orderNumber' => $order->getOrderNumber(),
                    'itemsToProcess' => $orderOperationData->getItems(),
                ]);
            }
        } catch (Exception $exception) {
            $this->logger->critical('Exception during bidirectionality. (Exception: ' . $exception->getMessage() . ')', [
                'order' => $order->getId(),
                'transactionId' => $ratepayData->getTransactionId(),
                'orderNumber' => $order->getOrderNumber(),
                'itemsToProcess' => $orderOperationData->getItems(),
            ]);
        }

        $event->getContext()->removeExtension('ratepay');
    }
}
