<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Subscriber;

use Ratepay\RatepayPayments\Components\Checkout\Event\RatepayPaymentFilterEvent;
use Ratepay\RatepayPayments\Components\ProfileConfig\Model\Collection\ProfileConfigMethodCollection;
use Ratepay\RatepayPayments\Components\ProfileConfig\Service\ProfileConfigService;
use Ratepay\RatepayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentFilterSubscriber implements EventSubscriberInterface
{

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var ProfileConfigService
     */
    private $profileConfigService;
    /**
     * @var CartService
     */
    private $cartService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ProfileConfigService $profileConfigService,
        CartService $cartService
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->profileConfigService = $profileConfigService;
        $this->cartService = $cartService;
    }

    public static function getSubscribedEvents(): array
    {
        // ToDo: It's not enough to do it for the confirm page of the storefront. We have to do it for every sales
        //       channel, but there is no central event for it right now
        return [
            CheckoutConfirmPageLoadedEvent::class => ['dispatchRatepayFilterEvent', 320],
            RatepayPaymentFilterEvent::class => ['filterByDefaultConditions', 600]
        ];
    }

    public function dispatchRatepayFilterEvent(CheckoutConfirmPageLoadedEvent $event): void
    {
        $paymentMethods = $event->getPage()->getPaymentMethods();
        $paymentMethods = $paymentMethods->filter(function (PaymentMethodEntity $paymentMethod) use ($event) {
            if (MethodHelper::isRatepayMethod($paymentMethod->getHandlerIdentifier()) === false) {
                return true;
            }

            $profileConfig = $this->profileConfigService->getProfileConfigBySalesChannel(
                $event->getSalesChannelContext(),
                $paymentMethod->getId()
            );

            if ($profileConfig === null) {
                return false;
            }

            /** @var ProfileConfigMethodCollection $methodConfigs */
            $methodConfigs = $profileConfig->getPaymentMethodConfigs()->filterByMethod($paymentMethod->getId());
            $methodConfig = $methodConfigs->first();

            if ($methodConfig === null) {
                return null;
            }

            /** @var RatepayPaymentFilterEvent $filterEvent */
            $filterEvent = $this->eventDispatcher->dispatch(new RatepayPaymentFilterEvent(
                $paymentMethod,
                $profileConfig,
                $methodConfig,
                $event->getSalesChannelContext(),
                $event
            ));
            return $filterEvent->isAvailable();
        });
        $event->getPage()->setPaymentMethods($paymentMethods);
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
