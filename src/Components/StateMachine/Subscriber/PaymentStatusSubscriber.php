<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\StateMachine\Subscriber;

use Ratepay\RpayPayments\Core\Entity\Extension\OrderExtension;
use Ratepay\RpayPayments\Core\Entity\Extension\OrderLineItemExtension;
use Ratepay\RpayPayments\Core\Entity\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Core\Entity\RatepayOrderLineItemDataEntity;
use Ratepay\RpayPayments\Core\Entity\RatepayPositionEntity;
use Ratepay\RpayPayments\Core\Event\OrderItemOperationDoneEvent;
use Ratepay\RpayPayments\Core\PluginConfigService;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\System\StateMachine\Exception\IllegalTransitionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentStatusSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly OrderTransactionStateHandler $transactionStateHandler,
        private readonly PluginConfigService $configService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderItemOperationDoneEvent::class => 'onItemsOperationDone',
        ];
    }

    public function onItemsOperationDone(OrderItemOperationDoneEvent $event): void
    {
        if (!$this->configService->isUpdatePaymentStatusOnOrderItemOperation()) {
            return;
        }

        $transaction = $event->getOrderOperationData()->getTransaction();

        $positions = array_map(static function (OrderLineItemEntity $item): ?array {
            $ratepayData = $item->getExtension(OrderLineItemExtension::EXTENSION_NAME);

            if (!$ratepayData instanceof RatepayOrderLineItemDataEntity) {
                return null;
            }

            return [
                'qty' => $item->getQuantity(),
                'position' => $ratepayData->getPosition(),
            ];
        }, $event->getOrderEntity()->getLineItems()->getElements());

        $ratepayData = $event->getOrderEntity()->getExtension(OrderExtension::EXTENSION_NAME);
        if ($ratepayData instanceof RatepayOrderDataEntity && $ratepayData->getShippingPosition()) {
            $positions[] = [
                'qty' => 1,
                'position' => $ratepayData->getShippingPosition(),
            ];
        }

        $positions = array_filter($positions);

        if ($positions === []) {
            return;
        }

        $fullDelivered = true;
        $fullCanceled = true;
        $fullRefunded = true;

        $outstandingItemQty = 0;
        $deliveredItemQty = 0;
        $refundedItemQty = 0;
        $canceledItemQty = 0;
        foreach ($positions as $item) {
            /** @var RatepayPositionEntity $position */
            $position = $item['position'];

            /** @var int $qty */
            $qty = $item['qty'];

            $deliveredQty = ($position->getDelivered() + $position->getCanceled());
            if ($deliveredQty !== $qty) {
                $fullDelivered = false;
            }

            if ($position->getCanceled() !== $qty) {
                $fullCanceled = false;
            }

            if ($deliveredQty !== $qty || $position->getReturned() !== $position->getDelivered()) {
                $fullRefunded = false;
            }

            // do not respect the returned items, because they are not relevant.
            $outstandingItemQty += $qty - $position->getDelivered() - $position->getCanceled();
            $deliveredItemQty += $position->getDelivered();
            $refundedItemQty += $position->getReturned();
            $canceledItemQty += $position->getCanceled();
        }

        try {
            if ($fullCanceled && $canceledItemQty > 0) {
                $this->transactionStateHandler->cancel($transaction->getId(), $event->getContext());
            } elseif ($fullRefunded && $refundedItemQty > 0) {
                $this->transactionStateHandler->refund($transaction->getId(), $event->getContext());
            } elseif ($fullDelivered && $deliveredItemQty > 0 && $refundedItemQty === 0) {
                $this->transactionStateHandler->paid($transaction->getId(), $event->getContext());
            } elseif ($deliveredItemQty > 0 && $outstandingItemQty > 0) {
                $this->transactionStateHandler->payPartially($transaction->getId(), $event->getContext());
            } elseif ($refundedItemQty > 0) {
                $this->transactionStateHandler->refundPartially($transaction->getId(), $event->getContext());
            }
        } catch (IllegalTransitionException) {
            // do nothing.
        }
    }
}
