<?php

namespace Ratepay\RpayPayments\Components\ProfileConfig\Service\Search;

use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileConfigSearch;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Util\AddressUtil;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProfileBySalesChannelContextAndCart implements ProfileSearchInterface
{

    private ProfileSearchService $searchService;

    public function __construct(
        ProfileSearchService $searchService
    )
    {
        $this->searchService = $searchService;
    }

    /**
     * @return array<ProfileConfigEntity>|ProfileConfigEntity|null
     */
    public function createSearchObject(SalesChannelContext $salesChannelContext, Cart $cart): ?ProfileConfigSearch
    {
        if (($customer = $salesChannelContext->getCustomer()) === null ||
            ($billingAddress = $customer->getActiveBillingAddress()) === null ||
            ($shippingAddress = $customer->getActiveShippingAddress()) === null
        ) {
            return null;
        }

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