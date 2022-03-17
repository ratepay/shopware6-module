<?php

namespace Ratepay\RpayPayments\Components\ProfileConfig\Service\Search;

use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileConfigSearch;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProfileBySalesChannelContext implements ProfileSearchInterface
{

    private CartService $cartService;
    private ProfileSearchService $searchService;

    public function __construct(
        CartService $cartService,
        ProfileSearchService $searchService
    )
    {
        $this->cartService = $cartService;
        $this->searchService = $searchService;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return array<ProfileConfigEntity>|ProfileConfigEntity|null
     */
    public function createSearchObject(SalesChannelContext $salesChannelContext): ?ProfileConfigSearch
    {
        if (($customer = $salesChannelContext->getCustomer()) === null ||
            ($billingAddress = $customer->getActiveBillingAddress()) === null ||
            ($shippingAddress = $customer->getActiveShippingAddress()) === null
        ) {
            return null;
        }

        $billingCountryCode = $billingAddress->getCountry()->getIso();

        if ($shippingAddress) {
            $shippingCountryCode = $shippingAddress->getCountry()->getIso();
        } else {
            $shippingCountryCode = $billingCountryCode;
        }

        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        return (new ProfileConfigSearch())
            ->setPaymentMethodId($salesChannelContext->getPaymentMethod()->getId())
            ->setBillingCountryCode($billingCountryCode)
            ->setShippingCountryCode($shippingCountryCode)
            ->setSalesChannelId($salesChannelContext->getSalesChannelId())
            ->setCurrency($salesChannelContext->getCurrency()->getIsoCode())
            ->setNeedsAllowDifferentAddress($billingCountryCode !== $shippingCountryCode)
            ->setIsB2b(!empty($billingAddress->getCompany()))
            ->setTotalAmount($cart->getPrice()->getTotalPrice());
    }

    public function search(ProfileConfigSearch $profileConfigSearch): EntitySearchResult
    {
        return $this->searchService->search($profileConfigSearch);
    }

}
