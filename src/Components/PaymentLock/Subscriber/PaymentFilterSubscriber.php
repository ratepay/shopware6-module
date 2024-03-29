<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentLock\Subscriber;

use Ratepay\RpayPayments\Components\Checkout\Event\RatepayPaymentFilterEvent;
use Ratepay\RpayPayments\Components\PaymentLock\Service\LockService;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentFilterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LockService $lockService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RatepayPaymentFilterEvent::class => 'filterPayments',
        ];
    }

    public function filterPayments(RatepayPaymentFilterEvent $event): void
    {
        if ($event->getOrderEntity() instanceof OrderEntity) {
            $customerId = $event->getOrderEntity()->getOrderCustomer()->getCustomerId();
        } else {
            $customer = $event->getSalesChannelContext()->getCustomer();
            if (!$customer instanceof CustomerEntity) {
                // customer is not logged in, or customer session has not been started yet.
                return;
            }

            $customerId = $customer->getId();
        }

        $isLocked = $this->lockService->isPaymentLocked(
            $event->getPaymentMethod()->getId(),
            $customerId,
            $event->getSalesChannelContext()->getContext()
        );

        $event->setIsAvailable(!$isLocked);
    }
}
