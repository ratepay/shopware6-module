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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentFilterSubscriber implements EventSubscriberInterface
{
    private LockService $lockService;

    public function __construct(LockService $lockService)
    {
        $this->lockService = $lockService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RatepayPaymentFilterEvent::class => 'filterPayments',
        ];
    }

    public function filterPayments(RatepayPaymentFilterEvent $event): void
    {
        if ($event->getOrderEntity()) {
            $customerId = $event->getOrderEntity()->getOrderCustomer()->getCustomerId();
        } else {
            $customerId = $event->getSalesChannelContext()->getCustomer()->getId();
        }

        $isLocked = $this->lockService->isPaymentLocked(
            $event->getPaymentMethod()->getId(),
            $customerId,
            $event->getSalesChannelContext()->getContext()
        );

        $event->setIsAvailable($isLocked !== true);
    }
}
