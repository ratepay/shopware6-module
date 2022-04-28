<?php

namespace Ratepay\RpayPayments\Components\ProfileConfig\Service\Search;

use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileConfigSearch;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Util\AddressUtil;
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

        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        return (new ProfileConfigSearch())
            ->setPaymentMethodId($salesChannelContext->getPaymentMethod()->getId())
            ->setBillingCountryCode($billingAddress->getCountry()->getIso())
            ->setShippingCountryCode($shippingAddress->getCountry()->getIso())
            ->setSalesChannelId($salesChannelContext->getSalesChannelId())
            ->setCurrency($salesChannelContext->getCurrency()->getIsoCode())
            ->setNeedsAllowDifferentAddress(!AddressUtil::areCustomerAddressObjectsIdentical($billingAddress, $shippingAddress))
            ->setIsB2b(!empty($billingAddress->getCompany()))
            ->setTotalAmount($cart->getPrice()->getTotalPrice());
    }

    public function search(ProfileConfigSearch $profileConfigSearch): EntitySearchResult
    {
        return $this->searchService->search($profileConfigSearch);
    }

}
