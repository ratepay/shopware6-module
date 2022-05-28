<?php

namespace Ratepay\RpayPayments\Tests\Components\ProfileConfig\Service\Search;

use PHPUnit\Framework\TestCase;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileByOrderEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileSearchService;
use Ratepay\RpayPayments\Tests\Mock\Model\CountryMock;
use Ratepay\RpayPayments\Tests\Mock\Model\OrderMock;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;

class ProfileByOrderEntityTest extends TestCase
{

    public function testCreateSearchObject()
    {
        $order = OrderMock::createMock();

        $cartPrice = $this->createMock(CartPrice::class);
        $cartPrice->method('getTotalPrice')->willReturn(119.0);
        $order->setPrice($cartPrice);
        $shippingOrderAddress = $order->getDeliveries()->first()->getShippingOrderAddress();
        $shippingOrderAddress->setCountry(CountryMock::createMock('AT'));
        $shippingOrderAddress->setCountryId($shippingOrderAddress->getCountry()->getId());


        $searchService = new ProfileByOrderEntity(
            $this->createMock(ProfileSearchService::class)
        );

        $searchObject = $searchService->createSearchObject($order);

        self::assertEquals(119.0, $searchObject->getTotalAmount());
        self::assertFalse($searchObject->isB2b());
        self::assertEquals('DE', $searchObject->getBillingCountryCode());
        self::assertEquals('AT', $searchObject->getShippingCountryCode());
        self::assertTrue($searchObject->isNeedsAllowDifferentAddress());


        // change Street for testing `isNeedsAllowDifferentAddress`
        $order->getDeliveries()->first()->getShippingOrderAddress()->setStreet('other street');
        $searchObject = $searchService->createSearchObject($order);
        self::assertTrue($searchObject->isNeedsAllowDifferentAddress());
        // more tests for `isNeedsAllowDifferentAddress` are not required, cause there a separate unit tests for it


        $order->getBillingAddress()->setCompany('my company');
        $searchObject = $searchService->createSearchObject($order);
        self::assertTrue($searchObject->isB2b());

    }

}
