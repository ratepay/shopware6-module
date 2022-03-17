<?php

namespace Ratepay\RpayPayments\Components\ProfileConfig\Service\Search;

use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileConfigSearch;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
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
        $shippingAddress = $order->getAddresses()->get($order->getDeliveries()->first()->getShippingOrderAddressId());

        $billingCountryCode = $billingAddress->getCountry()->getIso();

        if ($shippingAddress) {
            $shippingCountryCode = $shippingAddress->getCountry()->getIso();
        } else {
            $shippingCountryCode = $billingCountryCode;
        }

        return (new ProfileConfigSearch())
            ->setPaymentMethodId(($transaction = $order->getTransactions()->last()) ? $transaction->getPaymentMethodId() : null)
            ->setBillingCountryCode($billingCountryCode)
            ->setShippingCountryCode($shippingCountryCode)
            ->setSalesChannelId($order->getSalesChannelId())
            ->setCurrency($order->getCurrency()->getIsoCode())
            ->setNeedsAllowDifferentAddress($billingCountryCode !== $shippingCountryCode)
            ->setIsB2b(!empty($billingAddress->getCompany()))
            ->setTotalAmount($order->getPrice()->getTotalPrice());
    }

    public function search(ProfileConfigSearch $profileConfigSearch): EntitySearchResult
    {
        return $this->searchService->search($profileConfigSearch);
    }
}
