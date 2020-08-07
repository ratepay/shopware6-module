<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentLock\Subscriber;


use Ratepay\RatepayPayments\Components\Checkout\Event\RatepayPaymentFilterEvent;
use Ratepay\RatepayPayments\Components\PaymentLock\Service\LockService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentFilterSubscriber implements EventSubscriberInterface
{

    /**
     * @var LockService
     */
    private $lockService;

    public function __construct(LockService $lockService)
    {
        $this->lockService = $lockService;
    }

    public static function getSubscribedEvents()
    {
        return [
            RatepayPaymentFilterEvent::class => 'filterPayments'
        ];
    }

    public function filterPayments(RatepayPaymentFilterEvent $event)
    {
        $isLocked = $this->lockService->isPaymentLocked(
            $event->getPaymentMethod()->getId(),
            $event->getSalesChannelContext()->getCustomer()->getId(),
            $event->getSalesChannelContext()->getContext()
        );

        $event->setIsAvailable($isLocked !== true);
    }

}
