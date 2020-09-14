<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentLock\Subscriber;


use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentFailedEvent;
use Ratepay\RpayPayments\Components\PaymentLock\Service\LockService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentFailedSubscriber implements EventSubscriberInterface
{
    public const ERROR_CODES = [703, 720, 721];

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
            PaymentFailedEvent::class => 'lockPaymentMethod'
        ];
    }

    public function lockPaymentMethod(PaymentFailedEvent $event)
    {
        if ($event->getResponse() &&
            in_array($event->getResponse()->getReasonCode(), self::ERROR_CODES, false)
        ) {
            if ($event->getSalesChannelContext()->getCustomer() === null) {
                // customer is not logged in - guest order
                return;
            }
            $this->lockService->lockPaymentMethod(
                $event->getTransaction()->getOrderTransaction()->getPaymentMethodId(),
                $event->getSalesChannelContext()->getCustomer()->getId(),
                $event->getContext()
            );
        }
    }
}
