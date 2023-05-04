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
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProfileBySalesChannelContextAndCart implements ProfileSearchInterface
{
    public function __construct(
        private readonly ProfileSearchService $searchService
    ) {
    }

    public function createSearchObject(SalesChannelContext $salesChannelContext, Cart $cart): ?ProfileConfigSearch
    {
        if (!($customer = $salesChannelContext->getCustomer()) instanceof CustomerEntity ||
            !($billingAddress = $customer->getActiveBillingAddress()) instanceof CustomerAddressEntity ||
            !($shippingAddress = $customer->getActiveShippingAddress()) instanceof CustomerAddressEntity
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
