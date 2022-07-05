<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\StateMachine\Subscriber;

use Exception;
use Monolog\Logger;
use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentCancelService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentDeliverService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentReturnService;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransitionSubscriber implements EventSubscriberInterface
{
    private ConfigService $configService;

    private PaymentDeliverService $paymentDeliverService;

    private PaymentCancelService $paymentCancelService;

    private PaymentReturnService $paymentReturnService;

    private EntityRepository $orderDeliveryRepository;

    private EntityRepository $orderRepository;

    private Logger $logger;

    public function __construct(
        EntityRepository $orderDeliveryRepository,
        EntityRepository $orderRepository,
        ConfigService $configService,
        PaymentDeliverService $paymentDeliverService,
        PaymentCancelService $paymentCancelService,
        PaymentReturnService $paymentReturnService,
        Logger $logger
    )
    {
        $this->orderDeliveryRepository = $orderDeliveryRepository;
        $this->orderRepository = $orderRepository;
        $this->configService = $configService;
        $this->paymentDeliverService = $paymentDeliverService;
        $this->paymentCancelService = $paymentCancelService;
        $this->paymentReturnService = $paymentReturnService;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StateMachineTransitionEvent::class => 'onTransition',
        ];
    }

    public function onTransition(StateMachineTransitionEvent $event): void
    {
        if ($event->getEntityName() !== 'order_delivery' || $this->configService->isBidirectionalityEnabled() === false) {
            return;
        }

        /** @var OrderDeliveryEntity $orderDelivery */
        $orderDelivery = $this->orderDeliveryRepository->search(new Criteria([$event->getEntityId()]), $event->getContext())->first();
        /** @var OrderEntity $order */
        $order = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderDelivery->getOrderId()), $event->getContext())->first();

        if (!MethodHelper::isRatepayOrder($order)) {
            return;
        }

        /** @var RatepayOrderDataEntity $ratepayData */
        $ratepayData = $order->getExtension(OrderExtension::EXTENSION_NAME);

        if ($ratepayData === null) {
            $this->logger->warning('Error during bidirectionality: No Ratepay Data was found.', [
                'order' => $order->getId(),
                'orderNumber' => $order->getOrderNumber(),
            ]);
            return;
        }

        switch ($event->getToPlace()->getTechnicalName()) {
            case $this->configService->getBidirectionalityFullDelivery():
                $operation = OrderOperationData::OPERATION_DELIVER;
                $service = $this->paymentDeliverService;
                break;
            case $this->configService->getBidirectionalityFullCancel():
                $operation = OrderOperationData::OPERATION_CANCEL;
                $service = $this->paymentCancelService;
                break;
            case $this->configService->getBidirectionalityFullReturn():
                $operation = OrderOperationData::OPERATION_RETURN;
                $service = $this->paymentReturnService;
                break;
            default:
                // do nothing
                return;
        }

        $orderOperationData = new OrderOperationData($event->getContext(), $order, $operation, null, false);
        try {
            $response = $service->doRequest($orderOperationData);
            if ($response->getResponse()->isSuccessful() === false) {
                $this->logger->error('Error during bidirectionality. (Exception: ' . $response->getResponse()->getReasonMessage() . ')', [
                    'order' => $order->getId(),
                    'transactionId' => $ratepayData->getTransactionId(),
                    'orderNumber' => $order->getOrderNumber(),
                    'itemsToProcess' => $orderOperationData->getItems(),
                ]);
            }
        } catch (Exception $e) {
            $this->logger->critical('Exception during bidirectionality. (Exception: ' . $e->getMessage() . ')', [
                'order' => $order->getId(),
                'transactionId' => $ratepayData->getTransactionId(),
                'orderNumber' => $order->getOrderNumber(),
                'itemsToProcess' => $orderOperationData->getItems(),
            ]);
        }
    }
}
