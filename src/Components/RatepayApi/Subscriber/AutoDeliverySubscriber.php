<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Subscriber;

use Exception;
use Psr\Log\LoggerInterface;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentSuccessfulEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentDeliverService;
use Ratepay\RpayPayments\Core\PluginConfigService;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Product\State;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AutoDeliverySubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PaymentDeliverService $deliverService,
        private readonly LoggerInterface $logger,
        private readonly EntityRepository $orderRepository,
        private readonly PluginConfigService $configService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentSuccessfulEvent::class => 'onSuccess',
        ];
    }

    public function onSuccess(PaymentSuccessfulEvent $event): void
    {
        if ($this->configService->isAutoDeliveryOfVirtualProductsDisabled()) {
            return;
        }

        // we will reload the order completely to have all extension-data
        $order = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($event->getOrder()->getId()), $event->getContext())->first();

        if (!$order instanceof OrderEntity) {
            return; // should never occur - just to be safe.
        }

        $orderItems = $order->getLineItems();

        $lineItemsToDeliver = [];
        foreach ($orderItems as $lineItem) {
            if (in_array(State::IS_DOWNLOAD, $lineItem->getStates(), true)) {
                $lineItemsToDeliver[$lineItem->getId()] = $lineItem->getQuantity();
            }
        }

        if ($lineItemsToDeliver !== []) {
            try {
                $this->deliverService->doRequest(new OrderOperationData(
                    $event->getContext(),
                    $order,
                    OrderOperationData::OPERATION_DELIVER,
                    $lineItemsToDeliver,
                    false
                ));
            } catch (Exception $exception) {
                $this->logger->error('Error on auto-delivery of virtual-products: ' . $exception->getMessage(), [
                    'order-id' => $order->getId(),
                    'order-number' => $order->getOrderNumber(),
                    'virtual-line-items' => $lineItemsToDeliver,
                    'error-message' => $exception->getMessage(),
                ]);
            }
        }
    }
}
