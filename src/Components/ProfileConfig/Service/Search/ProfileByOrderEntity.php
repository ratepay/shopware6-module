<?php

namespace Ratepay\RpayPayments\Components\ProfileConfig\Service\Search;

use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileConfigSearch;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Util\AddressUtil;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

class ProfileByOrderEntity implements ProfileSearchInterface
{

    private ProfileSearchService $searchService;

    public function __construct(ProfileSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * @param OrderEntity $order
     * @return array<ProfileConfigEntity>|ProfileConfigEntity|null
     */
    public function createSearchObject(OrderEntity $order): ProfileConfigSearch
    {
        $billingAddress = $order->getAddresses()->get($order->getBillingAddressId());
        $shippingAddressId = $order->getDeliveries()->first()->getShippingOrderAddressId();
        $shippingAddress = $shippingAddressId ? $order->getAddresses()->get($shippingAddressId) : $billingAddress;

        return (new ProfileConfigSearch())
            ->setPaymentMethodId(($transaction = $order->getTransactions()->last()) ? $transaction->getPaymentMethodId() : null)
            ->setBillingCountryCode($billingAddress->getCountry()->getIso())
            ->setShippingCountryCode($shippingAddress->getCountry()->getIso())
            ->setSalesChannelId($order->getSalesChannelId())
            ->setCurrency($order->getCurrency()->getIsoCode())
            ->setNeedsAllowDifferentAddress(!AddressUtil::areOrderAddressObjectsIdentical($billingAddress, $shippingAddress))
            ->setIsB2b(!empty($billingAddress->getCompany()))
            ->setTotalAmount($order->getPrice()->getTotalPrice());
    }

    public function search(ProfileConfigSearch $profileConfigSearch): EntitySearchResult
    {
        return $this->searchService->search($profileConfigSearch);
    }
}
