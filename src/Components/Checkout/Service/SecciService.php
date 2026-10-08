<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Service;

use JetBrains\PhpStorm\ArrayShape;
use RatePAY\Model\Response\SecciRequest;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileBySalesChannelContextAndCart;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\SecciRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\SecciRequestService;
use Ratepay\RpayPayments\Exception\RatepayException;
use Ratepay\RpayPayments\Util\MethodHelper;
use Ratepay\RpayPayments\Util\RequestHelper;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Payment\PaymentMethodCollection;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RequestStack;

class SecciService
{
    public const SESSION_ATTRIBUTE_RATEPAY_ATTESTATION_TOKEN = 'ratepay_attestation_token';

    public function __construct(
        private readonly ProfileBySalesChannelContextAndCart $profileBySalesChannelContextAndCart,
        private readonly SecciRequestService                 $secciRequestService,
        private readonly RequestStack                        $requestStack,
        private readonly SystemConfigService                 $systemConfigService,
    )
    {
    }

    /**
     * Filter payment methods that require SECCI.
     */
    public function filterPaymentMethodRequiresSecci(PaymentMethodCollection $paymentMethods, Cart $cart, SalesChannelContext $salesChannelContext): PaymentMethodCollection
    {
        return $paymentMethods->filter(fn(PaymentMethodEntity $paymentMethod) => $this->paymentMethodRequiresSecci($paymentMethod, $cart, $salesChannelContext));
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
            $search = $this->profileBySalesChannelContextAndCart->createSearchObject($salesChannelContext, $cart);
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
     */
    #[ArrayShape(['id' => 'string', 'data' => 'string', 'contentType' => 'string'])]
    public function prepareSecciPdf(RequestDataBag $requestDataBag, Cart $cart, SalesChannelContext $salesChannelContext): ?array
    {
        $response = $this->triggerSecciDeliveryAndSaveToken($requestDataBag, $cart, $salesChannelContext, false);

        if ($response != null && $response->isSuccessful()) {
            return [
                'id' => $response->getDocumentId(),
                'data' => $response->getDocument(),
                'contentType' => $response->getContentType(),
            ];
        }

        return null;
    }

    private function triggerSecciDeliveryAndSaveToken(RequestDataBag $requestDataBag, Cart $cart, SalesChannelContext $salesChannelContext, bool $sendMail, ?string $emailAddress = null): ?SecciRequest
    {
        $search = $this->profileBySalesChannelContextAndCart->createSearchObject($salesChannelContext, $cart);
        $profile = $this->profileBySalesChannelContextAndCart->search($search, $salesChannelContext);

        if (!$sendMail) {
            $emailAddress = null;
        } else {
            $emailAddress = $emailAddress ?? $salesChannelContext->getCustomer()->getEmail();
        }

        // We cannot reliably use context locale as the ContextSwitchEvent passes a context with the default locale instead of the current one
        $locale = $this->requestStack->getMainRequest()->getLocale() ?? $salesChannelContext->getLanguageInfo()->localeCode;

        $secciRequestData = new SecciRequestData(
            $sendMail ? SecciRequestData::DELIVERY_METHOD_EMAIL : SecciRequestData::DELIVERY_METHOD_PDF,
            $emailAddress,
            $locale,
            $salesChannelContext->getCustomer()->getActiveBillingAddress()->getCountry()->getIso(),
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
            $this->requestStack->getSession()->getFlashBag()->add(StorefrontController::DANGER, $e->getMessage());
            return null;
        }
        /** @var SecciRequest $response */
        $response = $requestBuilder->getResponse();

        if ($response->isSuccessful()) {
            $token = $response->getAttestationToken();
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
                $ratepayParameters
            );
            return $response;
        }

        $this->requestStack->getSession()->getFlashBag()->add(StorefrontController::DANGER, $response->getReasonMessage());

        return $response;
    }

    private function saveAttestationToken(string $token, string $deliveryMethod, ?string $documentId, ?string $emailRecipient, float $cartTotal, string $paymentMethodId, string $currencyId, string $locale, array $ratepayParameters): void
    {
        $this->requestStack->getSession()->set(self::SESSION_ATTRIBUTE_RATEPAY_ATTESTATION_TOKEN, [
            'token' => $token,
            'deliveryMethod' => $deliveryMethod,
            'documentId' => $documentId,
            'emailRecipient' => $emailRecipient,
            'cartTotal' => $cartTotal,
            'paymentMethodId' => $paymentMethodId,
            'currencyId' => $currencyId,
            'locale' => $locale,
            'installmentHash' => $ratepayParameters['installment']['hash'] ?? null,
        ]);
    }

    #[ArrayShape(['token' => "string", 'deliveryMethod' => "string", 'documentId' => "string", 'emailRecipient' => "string", 'cartTotal' => "float", 'paymentMethodId' => "string", 'currencyId' => "string", 'locale' => "string", 'installmentHash' => "string|null"])]
    public function getAttestationTokenStorage(float $cartTotal, string $paymentMethodId, string $currencyId, string $locale, array $ratepayParameters = []): ?array
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

        // Validate financing data has not changed. Ignore secci parameters. Only for variant 2
        if ($this->systemConfigService->get('RpayPayments.config.ratepaySecciVariant') == 2) {
            if ($result['installmentHash'] !== ($ratepayParameters['installment']['hash'] ?? null)) {
                return null;
            }
        }

        // All parameters match saved token, return it
        return $result;
    }

    public function getAttestationToken(float $cartTotal, string $paymentMethodId, string $currencyId, string $locale, array $ratepayParameters = []): ?string
    {
        return $this->getAttestationTokenStorage($cartTotal, $paymentMethodId, $currencyId, $locale, $ratepayParameters)['token'] ?? null;
    }


    public function clearAttestationToken(): void
    {
        $this->requestStack->getSession()->remove(self::SESSION_ATTRIBUTE_RATEPAY_ATTESTATION_TOKEN);
    }
}
