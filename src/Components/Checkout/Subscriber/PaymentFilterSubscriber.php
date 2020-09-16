<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Subscriber;

use Ratepay\RpayPayments\Components\Checkout\Event\RatepayPaymentFilterEvent;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressCollection;
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
            RatepayPaymentFilterEvent::class => ['filterByDefaultConditions', self::PAYMENT_FILTER_PRIORITY],
        ];
    }

    public function filterByDefaultConditions(RatepayPaymentFilterEvent $event): RatepayPaymentFilterEvent
    {
        $methodConfig = $event->getMethodConfig();
        $salesChannelContext = $event->getSalesChannelContext();

        $orderEntity = $event->getOrderEntity();
        if ($orderEntity) {
            // order has been already placed
            $addressCollection = $orderEntity->getAddresses();
            $billingAddressId = $orderEntity->getBillingAddressId();
            $shippingAddressId = $orderEntity->getBillingAddressId();
            $totalPrice = $orderEntity->getPrice()->getTotalPrice();
        } else {
            // order has not been placed
            $customer = $salesChannelContext->getCustomer();
            $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

            if ($customer === null ||
                $customer->getActiveBillingAddress() === null ||
                $customer->getActiveShippingAddress() === null
            ) {
                $event->setIsAvailable(false);

                return $event;
            }
            $addressCollection = new CustomerAddressCollection([
                $customer->getActiveBillingAddress()->getId() => $customer->getActiveBillingAddress(),
                $customer->getActiveShippingAddress()->getId() => $customer->getActiveShippingAddress(),
            ]);
            $billingAddressId = $customer->getActiveBillingAddress()->getId();
            $shippingAddressId = $customer->getActiveShippingAddress()->getId();
            $totalPrice = $cart->getPrice()->getTotalPrice();
        }

        $isB2b = !empty($addressCollection->get($billingAddressId)->getCompany());

        if ($isB2b && $methodConfig->isAllowB2b() === false) {
            $event->setIsAvailable(false);

            return $event;
        }

        $amountMin = $methodConfig->getLimitMin();
        $amountMax = $isB2b ? $methodConfig->getLimitMaxB2b() : $methodConfig->getLimitMax();
        if ($totalPrice < $amountMin || $totalPrice > $amountMax) {
            $event->setIsAvailable(false);

            return $event;
        }

        if ($billingAddressId !== $shippingAddressId &&
            $methodConfig->isAllowDifferentAddresses() === false
        ) {
            $event->setIsAvailable(false);

            return $event;
        }

        return $event;
    }
}
