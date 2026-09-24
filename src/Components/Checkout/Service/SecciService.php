<?php declare(strict_types=1);

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
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class SecciService
{
    public function __construct(
        private readonly ProfileBySalesChannelContextAndCart $profileBySalesChannelContextAndCart,
        private readonly SecciRequestService                 $secciRequestService,
        private readonly RequestStack                        $requestStack,
    )
    {
    }

    public function triggerSecciEmail(Cart $cart, SalesChannelContext $salesChannelContext): bool
    {
        $search = $this->profileBySalesChannelContextAndCart->createSearchObject($salesChannelContext, $cart);
        $profile = $this->profileBySalesChannelContextAndCart->search($search, $salesChannelContext);

        $secciRequestData = new SecciRequestData(
            SecciRequestData::DELIVERY_METHOD_EMAIL,
            $salesChannelContext->getCustomer()->getEmail(),
            $salesChannelContext->getLanguageInfo()->localeCode,
            $salesChannelContext->getCustomer()->getActiveBillingAddress()->getCountry()->getIso(),
            $cart,
            $salesChannelContext->getPaymentMethod(),
            $salesChannelContext->getCurrency(),
            $salesChannelContext->getTaxState(),
            $profile->first(),
            $salesChannelContext->getContext(),
        );

        $requestBuilder = $this->secciRequestService->doRequest($secciRequestData);
        /** @var SecciRequest $response */
        $response = $requestBuilder->getResponse();

        if ($response->isSuccessful()) {
            $token = $response->getAttestationToken();
            // TODO save token in cart
            return true;
        }

        $session = $this->requestStack->getSession();
        if ($session instanceof Session) {
            $session->getFlashBag()->add(StorefrontController::DANGER, $response->getReasonMessage());
        }

        return false;
    }

    #[ArrayShape(['id' => 'string', 'data' => 'string', 'contentType' => 'string'])]
    public function prepareSecciPdf(Cart $cart, SalesChannelContext $salesChannelContext): ?array
    {
        $search = $this->profileBySalesChannelContextAndCart->createSearchObject($salesChannelContext, $cart);
        $profile = $this->profileBySalesChannelContextAndCart->search($search, $salesChannelContext);

        $secciRequestData = new SecciRequestData(
            SecciRequestData::DELIVERY_METHOD_PDF,
            null,
            $salesChannelContext->getLanguageInfo()->localeCode,
            $salesChannelContext->getCustomer()->getActiveBillingAddress()->getCountry()->getIso(),
            $cart,
            $salesChannelContext->getPaymentMethod(),
            $salesChannelContext->getCurrency(),
            $salesChannelContext->getTaxState(),
            $profile->first(),
            $salesChannelContext->getContext(),
        );

        $requestBuilder = $this->secciRequestService->doRequest($secciRequestData);
        /** @var SecciRequest $response */
        $response = $requestBuilder->getResponse();

        if ($response->isSuccessful()) {
            $token = $response->getAttestationToken();
            // TODO save token in cart
            return [
                'id' => $response->getDocumentId(),
                'data' => $response->getDocument(),
                'contentType' => $response->getContentType(),
            ];
        }

        $session = $this->requestStack->getSession();
        if ($session instanceof Session) {
            $session->getFlashBag()->add(StorefrontController::DANGER, $response->getReasonMessage());
        }

        return null;
    }
}