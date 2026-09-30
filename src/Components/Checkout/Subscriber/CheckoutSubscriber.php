<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Subscriber;

use Ratepay\RpayPayments\Components\AdminOrders\Service\SessionService;
use Ratepay\RpayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RpayPayments\Components\Checkout\Service\SecciService;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileBySalesChannelContextAndCart;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected ExtensionService $extensionService,
        protected SystemConfigService $systemConfigService,
        protected ProfileBySalesChannelContextAndCart $profileBySalesChannelContextAndCart,
        protected SecciService $secciService,
        protected SessionService $sessionService,
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

    private function addSecciData(CheckoutConfirmPageLoadedEvent $event, Struct $extension): void
    {
        $salesChannelContext = $event->getSalesChannelContext();

        $secciVariant = $this->systemConfigService->get('RpayPayments.config.ratepaySecciVariant', $salesChannelContext->getSalesChannelId()) ?? 1;
        if ($this->sessionService->isAdminSession($salesChannelContext, $event->getRequest()->getSession())) {
            $secciVariant = 3;
        }
        $disablePaymentModePreselection = false;

        if ($secciVariant == 1) {
            $showSecciBanner = $this->secciService->anyPaymentMethodRequiresSecci($event->getPage()->getPaymentMethods(), $event->getPage()->getCart(), $event->getSalesChannelContext());

            if (
                $this->secciService->paymentMethodRequiresSecci($salesChannelContext->getPaymentMethod(), $event->getPage()->getCart(), $salesChannelContext)
                && !$this->secciService->hasAttestationToken()
            ) {
                $disablePaymentModePreselection = true;
            }
        } else {
            $showSecciBanner = $this->secciService->paymentMethodRequiresSecci($salesChannelContext->getPaymentMethod(), $event->getPage()->getCart(), $salesChannelContext);
        }

        $extension->assign([
            'showSecciBanner' => $showSecciBanner,
            'secciVariant' => $secciVariant,
            'disablePaymentModePreselection' => $disablePaymentModePreselection
        ]);
    }

    private function addPaymentData(CheckoutConfirmPageLoadedEvent $event, ArrayStruct|Struct $extension): void
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
}
