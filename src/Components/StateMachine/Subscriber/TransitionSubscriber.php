<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\StateMachine\Subscriber;


use Exception;
use Monolog\Logger;
use Ratepay\RatepayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RatepayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RatepayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentCancelService;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentDeliverService;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentReturnService;
use Ratepay\RatepayPayments\Util\CriteriaHelper;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TransitionSubscriber implements EventSubscriberInterface
{

    /**
     * @var ConfigService
     */
    private $configService;
    /**
     * @var PaymentDeliverService
     */
    private $paymentDeliverService;
    /**
     * @var PaymentCancelService
     */
    private $paymentCancelService;
    /**
     * @var PaymentReturnService
     */
    private $paymentReturnService;
    /**
     * @var EntityRepositoryInterface
     */
    private $orderDeliveryRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        EntityRepositoryInterface $orderDeliveryRepository,
        EntityRepositoryInterface $orderRepository,
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

    public static function getSubscribedEvents()
    {
        return [
            StateMachineTransitionEvent::class => 'onTransition'
        ];
    }

    public function onTransition(StateMachineTransitionEvent $event)
    {
        if ($event->getEntityName() !== 'order_delivery' || $this->configService->isBidirectionalityEnabled() === false) {
            return;
        }

        /** @var OrderDeliveryEntity $orderDelivery */
        $orderDelivery = $this->orderDeliveryRepository->search(new Criteria([$event->getEntityId()]), $event->getContext())->first();
        /** @var OrderEntity $order */
        $order = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderDelivery->getOrderId()), $event->getContext())->first();

        /** @var RatepayOrderDataEntity $ratepayData */
        $ratepayData = $order->getExtension(OrderExtension::RATEPAY_DATA);

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

        $orderOperationData = new OrderOperationData($order, $operation, null, false);
        try {
            $response = $service->doRequest($event->getContext(), $orderOperationData);
            if ($response->getResponse()->isSuccessful() === false) {
                $this->logger->addError('Error during bidirectionality. (Exception: ' . $response->getResponse()->getReasonMessage() . ')', [
                    'order' => $order->getId(),
                    'transactionId' => $ratepayData->getTransactionId(),
                    'orderNumber' => $order->getOrderNumber(),
                    'itemsToProcess' => $orderOperationData->getItems(),
                ]);
            }
        } catch (Exception $e) {
            $this->logger->addCritical('Exception during bidirectionality. (Exception: ' . $e->getMessage() . ')', [
                'order' => $order->getId(),
                'transactionId' => $ratepayData->getTransactionId(),
                'orderNumber' => $order->getOrderNumber(),
                'itemsToProcess' => $orderOperationData->getItems()
            ]);
        }


    }
}
