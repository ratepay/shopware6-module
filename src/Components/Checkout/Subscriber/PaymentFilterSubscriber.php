<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Subscriber;

use Ratepay\RatepayPayments\Components\Checkout\Event\RatepayPaymentFilterEvent;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentFilterSubscriber implements EventSubscriberInterface
{

    public const PAYMENT_FILTER_PRIORITY = 600;

    /**
     * @var CartService
     */
    private $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RatepayPaymentFilterEvent::class => ['filterByDefaultConditions', self::PAYMENT_FILTER_PRIORITY]
        ];
    }

    public function filterByDefaultConditions(RatepayPaymentFilterEvent $event): RatepayPaymentFilterEvent
    {
        if ($event->isAvailable() === false) {
            return $event;
        }

        $salesChannelContext = $event->getSalesChannelContext();
        $methodConfig = $event->getMethodConfig();
        $customer = $salesChannelContext->getCustomer();
        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        if ($customer === null ||
            $customer->getActiveBillingAddress() === null ||
            $customer->getActiveShippingAddress() === null
        ) {
            $event->setIsAvailable(false);
            return $event;
        }

        $isB2b = !empty($customer->getActiveBillingAddress()->getCompany());

        if ($isB2b && $methodConfig->isAllowB2b() === false) {
            $event->setIsAvailable(false);
            return $event;
        }

        $amountMin = $methodConfig->getLimitMin();
        $amountMax = $isB2b ? $methodConfig->getLimitMaxB2b() : $methodConfig->getLimitMax();
        $totalPrice = $cart->getPrice()->getTotalPrice();
        if ($totalPrice < $amountMin || $totalPrice > $amountMax) {
            $event->setIsAvailable(false);
            return $event;
        }

        if ($methodConfig->isAllowDifferentAddresses() === false &&
            $customer->getActiveBillingAddress()->getId() !== $customer->getActiveShippingAddress()->getId()
        ) {
            $event->setIsAvailable(false);
            return $event;
        }

        return $event;
    }
}
