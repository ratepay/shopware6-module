<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Service\Search;

use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileConfigSearch;
use Ratepay\RpayPayments\Components\ProfileConfig\Util\AddressUtil;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;

class ProfileByOrderEntity implements ProfileSearchInterface
{
    public function __construct(
        private readonly ProfileSearchService $searchService
    ) {
    }

    public function createSearchObject(OrderEntity $order): ProfileConfigSearch
    {
        $billingAddress = $order->getAddresses()->get($order->getBillingAddressId());
        $shippingAddressId = $order->getDeliveries()->first()?->getShippingOrderAddressId();
        $shippingAddress = $shippingAddressId !== null ? $order->getAddresses()->get($shippingAddressId) : $billingAddress;

        return (new ProfileConfigSearch())
            ->setPaymentMethodId((($transaction = $order->getTransactions()->last()) instanceof OrderTransactionEntity) ? $transaction->getPaymentMethodId() : null)
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
