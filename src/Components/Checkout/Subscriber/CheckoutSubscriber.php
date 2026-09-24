<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Subscriber;

use Ratepay\RpayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileConfigSearch;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileBySalesChannelContextAndCart;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileSearchService;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected ExtensionService $extensionService,
        protected SystemConfigService $systemConfigService,
        protected ProfileBySalesChannelContextAndCart $profileBySalesChannelContextAndCart,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => ['addRatepayTemplateData', 310],
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    public function addRatepayTemplateData(CheckoutConfirmPageLoadedEvent $event): void
    {
        $extension = $event->getPage()->getExtension(ExtensionService::PAYMENT_PAGE_EXTENSION_NAME) ?? new ArrayStruct();

        $this->addSecciData($event, $extension);
        $this->addPaymentData($event, $extension);

        $event->getPage()->addExtension(ExtensionService::PAYMENT_PAGE_EXTENSION_NAME, $extension);
    }

    private function addSecciData(CheckoutConfirmPageLoadedEvent $event, ArrayStruct|\Shopware\Core\Framework\Struct\Struct $extension): void
    {
        $salesChannelContext = $event->getSalesChannelContext();

        $secciVariant = $this->systemConfigService->get('RpayPayments.config.ratepaySecciVariant', $salesChannelContext->getSalesChannelId()) ?? 1;

        if ($secciVariant == 1) {
            $showSecciBanner = $this->anyPaymentMethodRequiresSecci($event);
        } else {
            $showSecciBanner = $this->paymentMethodRequiresSecci($salesChannelContext->getPaymentMethod(), $salesChannelContext, $event->getPage()->getCart());
        }

        $extension->assign([
            'showSecciBanner' => $showSecciBanner,
            'secciVariant' => $secciVariant,
        ]);
    }

    private function addPaymentData(CheckoutConfirmPageLoadedEvent $event, ArrayStruct|\Shopware\Core\Framework\Struct\Struct $extension): void
    {
        $paymentMethod = $event->getSalesChannelContext()->getPaymentMethod();
        if (MethodHelper::isRatepayMethod($paymentMethod->getHandlerIdentifier()) &&
            $event->getPage()->getPaymentMethods()->has($paymentMethod->getId())
        ) {
            $paymentDataExtension = $this->extensionService->buildPaymentDataExtension($event->getSalesChannelContext(), null, $event->getRequest());
            if ($paymentDataExtension instanceof ArrayStruct) {
                $extension->assign(['enableBillingForm' => true]);
                $extension->assign($paymentDataExtension->getVars());
            }
        }
    }

    /**
     * Check if any available Ratepay payment method requires SECCI.
     */
    private function anyPaymentMethodRequiresSecci(CheckoutConfirmPageLoadedEvent $event): bool
    {
        foreach ($event->getPage()->getPaymentMethods() as $paymentMethod) {
            if ($this->paymentMethodRequiresSecci($paymentMethod, $event->getSalesChannelContext(), $event->getPage()->getCart())) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the specific payment method requires SECCI.
     */
    private function paymentMethodRequiresSecci(PaymentMethodEntity $paymentMethod, SalesChannelContext $salesChannelContext, Cart $cart): bool {
        if (MethodHelper::isRatepayMethod($paymentMethod->getHandlerIdentifier())) {
            $search = $this->profileBySalesChannelContextAndCart->createSearchObject($salesChannelContext, $cart);
            $search->setPaymentMethodId($paymentMethod->getId());
            $result = $this->profileBySalesChannelContextAndCart->search($search, $salesChannelContext);

            foreach ($result as $profileConfig) {
                foreach ($profileConfig->getPaymentMethodConfigs() as $paymentMethodConfig) {
                    if ($paymentMethodConfig->getPaymentMethodId() === $paymentMethod->getId()) {
                        if ($paymentMethodConfig->isRequireSecci()) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}
