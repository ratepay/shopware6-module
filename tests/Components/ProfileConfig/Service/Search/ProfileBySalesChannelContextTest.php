<?php

namespace Ratepay\RpayPayments\Tests\Components\ProfileConfig\Service\Search;

use PHPUnit\Framework\TestCase;
use Ratepay\RpayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileBySalesChannelContext;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileSearchService;
use Ratepay\RpayPayments\Tests\Mock\Model\CountryMock;
use Ratepay\RpayPayments\Tests\Mock\Model\CurrencyMock;
use Ratepay\RpayPayments\Tests\Mock\Model\PaymentMethodMock;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProfileBySalesChannelContextTest extends TestCase
{

    public function testCreateSearchObject()
    {
        $customerEntity = $this->createCustomer();

        $cartPriceMock = $this->createMock(CartPrice::class);
        $cartPriceMock->method('getTotalPrice')->willReturn(119.0);
        $cartMock = $this->createMock(Cart::class);
        $cartMock->method('getPrice')->willReturn($cartPriceMock);
        $cartServiceMock = $this->createMock(CartService::class);
        $cartServiceMock->method('getCart')->willReturn($cartMock);

        $salesChannelMock = $this->createMock(SalesChannelContext::class);
        $salesChannelMock->method('getCustomer')->willReturn($customerEntity);
        $salesChannelMock->method('getCurrency')->willReturn(CurrencyMock::createMock('EUR'));
        $salesChannelMock->method('getToken')->willReturn('12345');
        $salesChannelMock->method('getSalesChannelId')->willReturn('987654');
        $salesChannelMock->method('getPaymentMethod')->willReturn(PaymentMethodMock::createMock(InvoicePaymentHandler::class));

        $searchService = new ProfileBySalesChannelContext(
            $cartServiceMock,
            $this->createMock(ProfileSearchService::class)
        );

        $searchObject = $searchService->createSearchObject($salesChannelMock);

        self::assertEquals(119.0, $searchObject->getTotalAmount());
        self::assertFalse($searchObject->isB2b());
        self::assertEquals('DE', $searchObject->getBillingCountryCode());
        self::assertEquals('DE', $searchObject->getShippingCountryCode());
        self::assertFalse($searchObject->isNeedsAllowDifferentAddress());


        // change Street for testing `isNeedsAllowDifferentAddress`
        $customerEntity->setActiveShippingAddress(clone $customerEntity->getActiveBillingAddress());
        $customerEntity->getActiveShippingAddress()->setStreet('other street');
        $searchObject = $searchService->createSearchObject($salesChannelMock);
        self::assertTrue($searchObject->isNeedsAllowDifferentAddress());
        // more tests for `isNeedsAllowDifferentAddress` are not required, cause there a separate unit tests for it


        $customerEntity->getActiveBillingAddress()->setCompany('my company');
        $searchObject = $searchService->createSearchObject($salesChannelMock);
        self::assertTrue($searchObject->isB2b());
    }

    private function createCustomer(): CustomerEntity
    {
        $countryDE = CountryMock::createMock('DE');
        $customer = new CustomerEntity();

        $billingAddress = new CustomerAddressEntity();
        $billingAddress->setFirstName('billing/shipping address');
        $billingAddress->setId(Uuid::randomHex());
        $billingAddress->setCountry($countryDE);
        $billingAddress->setCountryId($billingAddress->getCountry()->getId());

        $customer->setActiveBillingAddress($billingAddress);
        $customer->setDefaultBillingAddress($customer->getActiveBillingAddress());
        $customer->setDefaultBillingAddressId($customer->getDefaultBillingAddress()->getId());


        $shippingAddress = $billingAddress;

        $customer->setActiveShippingAddress($shippingAddress);
        $customer->setDefaultShippingAddress($customer->getActiveShippingAddress());
        $customer->setDefaultShippingAddressId($customer->getDefaultShippingAddress()->getId());

        return $customer;
    }

}
