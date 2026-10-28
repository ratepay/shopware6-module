<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Service;

use RatePAY\Model\Response\SecciRequest;
use Ratepay\RpayPayments\Components\AdminOrders\Service\SessionService;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileBySalesChannelContextAndCart;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\SecciRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\SecciRequestService;
use Ratepay\RpayPayments\Exception\RatepayException;
use Ratepay\RpayPayments\Util\MethodHelper;
use Ratepay\RpayPayments\Util\RequestHelper;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Payment\PaymentMethodCollection;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Adapter\Translation\AbstractTranslator;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;

class SecciService
{
    public const SESSION_ATTRIBUTE_RATEPAY_ATTESTATION_TOKEN = 'ratepay_attestation_token';

    public function __construct(
        private readonly ProfileBySalesChannelContextAndCart $profileBySalesChannelContextAndCart,
        private readonly SecciRequestService $secciRequestService,
        private readonly RequestStack $requestStack,
        private readonly SystemConfigService $systemConfigService,
        #[Autowire(service: Translator::class)]
        private readonly AbstractTranslator $translator,
        private readonly SessionService $sessionService,
    ) {
    }

    /**
     * Check active SECCI variant: 1 for automatic delivery, 2 for explicit interaction, 3 for MOTO.
     */
    public function getSecciVariant(SalesChannelContext $context): int
    {
        if ($this->sessionService->isAdminSession($context, $this->requestStack->getSession())) {
            return 3;
        }

        return $this->systemConfigService->getInt('RpayPayments.config.ratepaySecciVariant');
    }

    /**
     * Filter payment methods that require SECCI.
     */
    public function filterPaymentMethodRequiresSecci(PaymentMethodCollection $paymentMethods, Cart $cart, SalesChannelContext $salesChannelContext): PaymentMethodCollection
    {
        return $paymentMethods->filter(fn (PaymentMethodEntity $paymentMethod) => $this->paymentMethodRequiresSecci($paymentMethod, $cart, $salesChannelContext));
    }

    /**
     * Check if the specific payment method requires SECCI.
     */
    public function paymentMethodRequiresSecci(PaymentMethodEntity $paymentMethod, Cart $cart, SalesChannelContext $salesChannelContext): bool
    {
        return $this->paymentMethodIdRequiresSecci($paymentMethod->getHandlerIdentifier(), $paymentMethod->getId(), $cart, $salesChannelContext);
    }

    /**
     * Check if the specific payment method requires SECCI.
     */
    public function paymentMethodIdRequiresSecci(string $handlerIdentifier, string $paymentMethodId, Cart $cart, SalesChannelContext $salesChannelContext): bool
    {
        if (MethodHelper::isRatepayMethod($handlerIdentifier)) {
            if (!empty($salesChannelContext->getCustomer()?->getActiveBillingAddress()?->getCompany())) {
                // B2B customers never require SECCI
                return false;
            }

            $search = $this->profileBySalesChannelContextAndCart->createSearchObject($salesChannelContext, $cart);
            if ($search === null) {
                return false;
            }

            $search->setPaymentMethodId($paymentMethodId);
            $result = $this->profileBySalesChannelContextAndCart->search($search, $salesChannelContext);

            foreach ($result as $profileConfig) {
                foreach ($profileConfig->getPaymentMethodConfigs() as $paymentMethodConfig) {
                    if ($paymentMethodConfig->getPaymentMethodId() === $paymentMethodId) {
                        if ($paymentMethodConfig->isRequireSecci()) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Trigger SECCI email delivery and return the document ID.
     */
    public function triggerSecciEmail(RequestDataBag $requestDataBag, Cart $cart, SalesChannelContext $salesChannelContext): ?string
    {
        $response = $this->triggerSecciDeliveryAndSaveToken($requestDataBag, $cart, $salesChannelContext, true);

        return ($response !== null && $response->isSuccessful()) ? $response->getDocumentId() : null;
    }

    /**
     * Trigger SECCI email delivery with custom email address and return the document ID.
     */
    public function triggerMotoSecciEmail(string $emailAddress, RequestDataBag $requestDataBag, Cart $cart, SalesChannelContext $salesChannelContext): ?string
    {
        $response = $this->triggerSecciDeliveryAndSaveToken($requestDataBag, $cart, $salesChannelContext, true, $emailAddress);

        return ($response !== null && $response->isSuccessful()) ? $response->getDocumentId() : null;
    }

    /**
     * Trigger SECCI PDF generation and return an array with the document data.
     *
     * @return array{id: string, data: string, contentType: string|null}|null
     */
    public function prepareSecciPdf(RequestDataBag $requestDataBag, Cart $cart, SalesChannelContext $salesChannelContext): ?array
    {
        $response = $this->triggerSecciDeliveryAndSaveToken($requestDataBag, $cart, $salesChannelContext, false);

        if ($response !== null && $response->isSuccessful() && $response->getDocumentId() && $response->getDocument()) {
            return [
                'id'          => $response->getDocumentId(),
                'data'        => $response->getDocument(),
                'contentType' => $response->getContentType(),
            ];
        }

        return null;
    }

    public function hasAttestationToken(): bool
    {
        return $this->requestStack->getSession()->has(self::SESSION_ATTRIBUTE_RATEPAY_ATTESTATION_TOKEN);
    }

    private function saveAttestationToken(string $token, string $deliveryMethod, ?string $documentId, ?string $emailRecipient, float $cartTotal, string $paymentMethodId, string $currencyId, string $locale, string $billingCountry, array $ratepayParameters): void
    {
        $this->requestStack->getSession()->set(self::SESSION_ATTRIBUTE_RATEPAY_ATTESTATION_TOKEN, [
            'token'           => $token,
            'deliveryMethod'  => $deliveryMethod,
            'documentId'      => $documentId,
            'emailRecipient'  => $emailRecipient,
            'cartTotal'       => $cartTotal,
            'paymentMethodId' => $paymentMethodId,
            'currencyId'      => $currencyId,
            'locale'          => $locale,
            'billingCountry'  => $billingCountry,
            'installmentHash' => $ratepayParameters['installment']['hash'] ?? null,
            'paymentType'     => $ratepayParameters['installment']['paymentType'] ?? null,
        ]);
    }

    /**
     * @return array{token: string, deliveryMethod: string, documentId: string, emailRecipient: string, cartTotal: float, paymentMethodId: string, currencyId: string, locale: string, installmentHash: string|null, paymentType: string|null}|null
     */
    public function getAttestationTokenStorage(float $cartTotal, string $paymentMethodId, string $currencyId, string $locale, string $billingCountry, SalesChannelContext $context, array $ratepayParameters = []): ?array
    {
        $result = $this->requestStack->getSession()->get(self::SESSION_ATTRIBUTE_RATEPAY_ATTESTATION_TOKEN);
        if (empty($result)) {
            return null;
        }

        // Validate parameters
        if ($result['cartTotal'] !== $cartTotal) {
            return null;
        }
        if ($result['paymentMethodId'] !== $paymentMethodId) {
            return null;
        }
        if ($result['currencyId'] !== $currencyId) {
            return null;
        }
        if ($result['locale'] !== $locale) {
            return null;
        }
        if ($result['billingCountry'] !== $billingCountry) {
            return null;
        }

        // Validate financing data and payment type have not changed. Only for variant 2 and 3.
        if (in_array($this->getSecciVariant($context), [2, 3]) && !empty($ratepayParameters)) {
            if ($result['installmentHash'] !== ($ratepayParameters['installment']['hash'] ?? null)) {
                return null;
            }

            if ($result['paymentType'] !== ($ratepayParameters['installment']['paymentType'] ?? null)) {
                return null;
            }
        }

        // All parameters match saved token, return it
        return $result;
    }

    public function getAttestationToken(float $cartTotal, string $paymentMethodId, string $currencyId, string $locale, string $billingCountry, SalesChannelContext $context, array $ratepayParameters = []): ?string
    {
        return $this->getAttestationTokenStorage($cartTotal, $paymentMethodId, $currencyId, $locale, $billingCountry, $context, $ratepayParameters)['token'] ?? null;
    }

    public function clearAttestationToken(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_ATTRIBUTE_RATEPAY_ATTESTATION_TOKEN);
    }

    private function triggerSecciDeliveryAndSaveToken(RequestDataBag $requestDataBag, Cart $cart, SalesChannelContext $salesChannelContext, bool $sendMail, ?string $emailAddress = null): ?SecciRequest
    {
        $search  = $this->profileBySalesChannelContextAndCart->createSearchObject($salesChannelContext, $cart);
        $profile = $this->profileBySalesChannelContextAndCart->search($search, $salesChannelContext);

        if ($sendMail) {
            $emailAddress = $emailAddress ?? $salesChannelContext->getCustomer()->getEmail();
        } else {
            $emailAddress = null;
        }

        $locale         = $this->requestStack->getMainRequest()->attributes->get('ratepay_saved_locale') ?? $salesChannelContext->getLanguageInfo()->localeCode;
        $billingCountry = $salesChannelContext->getCustomer()->getActiveBillingAddress()->getCountry()->getIso();

        $secciRequestData = new SecciRequestData(
            $sendMail ? SecciRequestData::DELIVERY_METHOD_EMAIL : SecciRequestData::DELIVERY_METHOD_PDF,
            $emailAddress,
            $locale,
            $billingCountry,
            $cart,
            $salesChannelContext->getPaymentMethod(),
            $salesChannelContext->getCurrency(),
            $salesChannelContext->getTaxState(),
            $requestDataBag,
            $salesChannelContext,
            $profile->first(),
        );

        try {
            $requestBuilder = $this->secciRequestService->doRequest($secciRequestData);
        } catch (RatepayException $e) {
            $session = $this->requestStack->getSession();
            if ($session instanceof FlashBagAwareSessionInterface) {
                $session->getFlashBag()->add(StorefrontController::DANGER, $e->getMessage());
            }
            return null;
        }
        /** @var SecciRequest $response */
        $response = $requestBuilder->getResponse();

        if ($response->isSuccessful()) {
            $token             = $response->getAttestationToken();
            $ratepayParameters = RequestHelper::getRatepayData($requestDataBag)?->all() ?? [];
            $this->saveAttestationToken(
                $token,
                $sendMail ? 'mail' : 'pdf',
                $response->getDocumentId() ?? null,
                $emailAddress,
                $cart->getPrice()->getTotalPrice(),
                $salesChannelContext->getPaymentMethod()->getId(),
                $salesChannelContext->getCurrencyId(),
                $locale,
                $billingCountry,
                $ratepayParameters
            );
            return $response;
        }

        $errorMessage = $this->translator->trans('ratepay.storefront.checkout.secci.complianceServiceErrorMessage');
        $session      = $this->requestStack->getSession();
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add(StorefrontController::DANGER, $errorMessage);
        }

        return $response;
    }
}
