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
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannel\AbstractContextSwitchRoute;
use Shopware\Core\System\SalesChannel\SalesChannel\ContextSwitchRoute;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CheckoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected ExtensionService $extensionService,
        protected SystemConfigService $systemConfigService,
        protected ProfileBySalesChannelContextAndCart $profileBySalesChannelContextAndCart,
        protected SecciService $secciService,
        protected SessionService $sessionService,
        protected RequestStack $requestStack,
        #[Autowire(service: ContextSwitchRoute::class)]
        protected AbstractContextSwitchRoute $contextSwitchRoute,
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
        $this->deliverAutomaticSecci($event);

        $extension = $event->getPage()->getExtension(ExtensionService::PAYMENT_PAGE_EXTENSION_NAME) ?? new ArrayStruct();

        $this->addSecciData($event, $extension);
        $this->addPaymentData($event, $extension);

        $event->getPage()->addExtension(ExtensionService::PAYMENT_PAGE_EXTENSION_NAME, $extension);
    }

    private function deliverAutomaticSecci(CheckoutConfirmPageLoadedEvent $event): void
    {
        $salesChannelContext = $event->getSalesChannelContext();

        if ($this->secciService->getSecciVariant($salesChannelContext) !== 1) {
            // Only variant 1 triggers automatic SECCI delivery
            return;
        }

        if ($this->requestStack->getSession()->remove('ratepay_trigger_secci')) {
            $result = $this->secciService->triggerSecciEmail(new RequestDataBag(), $event->getPage()->getCart(), $salesChannelContext);
            if (!$result) {
                $this->requestStack->getSession()->set('secciFailed', true);
            }
        }
    }

    private function addSecciData(CheckoutConfirmPageLoadedEvent $event, Struct $extension): void
    {
        $salesChannelContext = $event->getSalesChannelContext();

        $secciVariant = $this->secciService->getSecciVariant($salesChannelContext);
        $motoDocumentId = null;
        $motoEmail = null;

        $secciPaymentMethods = $this->secciService->filterPaymentMethodRequiresSecci($event->getPage()->getPaymentMethods(), $event->getPage()->getCart(), $event->getSalesChannelContext());
        $paymentMethodRequiresSecci = $secciPaymentMethods->has($salesChannelContext->getPaymentMethod()->getId());

        if ($secciVariant === 1) {
            $showSecciBanner = $secciPaymentMethods->count() !== 0;
        } else {
            $showSecciBanner = $paymentMethodRequiresSecci;
        }

        $attestationTokenStorage = $this->secciService->getAttestationTokenStorage(
            $event->getPage()->getCart()->getPrice()->getTotalPrice(),
            $salesChannelContext->getPaymentMethod()->getId(),
            $salesChannelContext->getCurrencyId(),
            $salesChannelContext->getLanguageInfo()->localeCode,
            $salesChannelContext->getCustomer()->getActiveBillingAddress()->getCountry()->getIso(),
            $salesChannelContext,
        );

        $showSecciDeliveryConfirmation = $attestationTokenStorage !== null && $paymentMethodRequiresSecci;
        $secciDeliveryConfirmationType = $attestationTokenStorage['deliveryMethod'] ?? null;

        if ($secciVariant === 3) {
            $motoDocumentId = $attestationTokenStorage['documentId'] ?? null;
            $motoEmail = $attestationTokenStorage['emailRecipient'] ?? null;
        }

        $secciFailed = $this->requestStack->getSession()->get('secciFailed') ?? false;

        $extension->assign([
            'showSecciBanner' => $showSecciBanner,
            'secciVariant' => $secciVariant,
            'secciPaymentMethods' => $secciPaymentMethods,
            'showSecciDeliveryConfirmation' => $showSecciDeliveryConfirmation,
            'secciDeliveryConfirmationType' => $secciDeliveryConfirmationType,
            'showSecciFailedConfirmation' => $secciFailed,
            'motoDocumentId' => $motoDocumentId,
            'motoEmail' => $motoEmail,
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
                $extension->assign([
                    'enableBillingForm' => true,
                ]);
                $extension->assign($paymentDataExtension->getVars());
            }
        }
    }
}
