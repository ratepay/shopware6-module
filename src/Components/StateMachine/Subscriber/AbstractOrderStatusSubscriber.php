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
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\System\StateMachine\Exception\IllegalTransitionException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractOrderStatusSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            OrderItemOperationDoneEvent::class => 'onItemsOperationDone',
        ];
    }

    public function onItemsOperationDone(OrderItemOperationDoneEvent $event): void
    {
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
                $this->onFullCancel($event);
            } elseif ($fullRefunded && $refundedItemQty > 0) {
                $this->onFullRefund($event);
            } elseif ($fullDelivered && $deliveredItemQty > 0 && $refundedItemQty === 0) {
                $this->onFullDelivery($event);
            } elseif ($deliveredItemQty > 0 && $outstandingItemQty > 0) {
                $this->onPartlyDelivery($event);
            } elseif ($refundedItemQty > 0) {
                $this->onPartlyRefund($event);
            }
        } catch (IllegalTransitionException) {
            // do nothing.
        }
    }

    protected function onFullCancel(OrderItemOperationDoneEvent $event): void
    {
    }

    protected function onFullRefund(OrderItemOperationDoneEvent $event): void
    {
    }

    protected function onFullDelivery(OrderItemOperationDoneEvent $event): void
    {
    }

    protected function onPartlyDelivery(OrderItemOperationDoneEvent $event): void
    {
    }

    protected function onPartlyRefund(OrderItemOperationDoneEvent $event): void
    {
    }
}
