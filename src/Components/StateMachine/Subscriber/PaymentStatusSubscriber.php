<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\StateMachine\Subscriber;

use Ratepay\RpayPayments\Core\Event\OrderItemOperationDoneEvent;
use Ratepay\RpayPayments\Core\PluginConfigService;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;

class PaymentStatusSubscriber extends AbstractOrderStatusSubscriber
{
    public function __construct(
        private readonly OrderTransactionStateHandler $transactionStateHandler,
        private readonly PluginConfigService $configService
    ) {
    }

    public function onItemsOperationDone(OrderItemOperationDoneEvent $event): void
    {
        if (!$this->configService->isUpdatePaymentStatusOnOrderItemOperation()) {
            return;
        }

        parent::onItemsOperationDone($event);
    }

    protected function onFullCancel(OrderItemOperationDoneEvent $event): void
    {
        $this->transactionStateHandler->cancel(
            $event->getOrderOperationData()->getTransaction()->getId(),
            $event->getContext()
        );
    }

    protected function onFullRefund(OrderItemOperationDoneEvent $event): void
    {
        $this->transactionStateHandler->refund(
            $event->getOrderOperationData()->getTransaction()->getId(),
            $event->getContext()
        );
    }

    protected function onFullDelivery(OrderItemOperationDoneEvent $event): void
    {
        $this->transactionStateHandler->paid(
            $event->getOrderOperationData()->getTransaction()->getId(),
            $event->getContext()
        );
    }

    protected function onPartlyDelivery(OrderItemOperationDoneEvent $event): void
    {
        $this->transactionStateHandler->payPartially(
            $event->getOrderOperationData()->getTransaction()->getId(),
            $event->getContext()
        );
    }

    protected function onPartlyRefund(OrderItemOperationDoneEvent $event): void
    {
        $this->transactionStateHandler->refundPartially(
            $event->getOrderOperationData()->getTransaction()->getId(),
            $event->getContext()
        );
    }
}
