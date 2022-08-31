<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\RatepayApi\Factory;

use PHPUnit\Framework\TestCase;
use RatePAY\Model\Request\SubModel\Content\ShoppingBasket;
use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RpayPayments\Tests\Mock\Model\OrderMock;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Factory\Mock;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\System\Currency\CurrencyEntity;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ShoppingBasketFactoryTest extends TestCase
{
    use KernelTestBehaviour;

    public function testBaseData()
    {
        $factory = $this->getFactory();

        $requestData = $this->createRequestData();
        $shoppingBasket = $factory->getData($requestData);

        /** @var ShoppingBasket\Items\Item[] $items */
        $items = $shoppingBasket->getItems()->getItems();
        self::assertCount(3, $items);
        self::assertEquals('item-sku-1', $items[0]->getArticleNumber());
        self::assertEquals('Product No 1', $items[0]->getDescription());
        self::assertEquals(3, $items[0]->getQuantity());
        self::assertEquals(30, $items[0]->getUnitPriceGross());
        self::assertEquals(19, $items[0]->getTaxRate());

        self::assertEquals('item-sku-2', $items[1]->getArticleNumber());
        self::assertEquals('Product No 2', $items[1]->getDescription());
        self::assertEquals(10, $items[1]->getQuantity());
        self::assertEquals(5, $items[1]->getUnitPriceGross());
        self::assertEquals(7, $items[1]->getTaxRate());

        self::assertEquals('item-sku-3', $items[2]->getArticleNumber());
        self::assertEquals('Product No 3', $items[2]->getDescription());
        self::assertEquals(6, $items[2]->getQuantity());
        self::assertEquals(50, $items[2]->getUnitPriceGross());
        self::assertEquals(19, $items[2]->getTaxRate());

        $discount = $shoppingBasket->getDiscount();
        self::assertNotNull($discount);
        self::assertEquals('discount', $discount->getDescription());
        self::assertEquals('Discount No 1, Discount No 2', $discount->getDescriptionAddition());
        self::assertEquals(-(3 + 10), $discount->getUnitPriceGross());
        self::assertEquals(19, $discount->getTaxRate());

        $shipping = $shoppingBasket->getShipping();
        self::assertNotNull($shipping);
        self::assertEquals('shipping', $shipping->getDescription());
        self::assertEquals(4.90, $shipping->getUnitPriceGross());
        self::assertEquals(19, $shipping->getTaxRate());
    }

    public function testInvalidItem()
    {
        $factory = $this->getFactory();

        $requestData = $this->createRequestData();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('unknown-id does not belongs to the order ' . $requestData->getOrder()->getId());

        $requestData = new OrderOperationData(Context::createDefaultContext(), $requestData->getOrder(), $requestData->getOperation(), array_merge(
            $requestData->getItems(),
            [
                'unknown-id' => 'unknown-id',
            ]
        ), false);

        $factory->getData($requestData);
    }

    /**
     * @depends testBaseData
     */
    public function testPartial()
    {
        $factory = $this->getFactory();

        $requestData = $this->createRequestData(['item-id-1' => 3, 'item-id-2' => 2]);

        $basket = $factory->getData($requestData);
        /** @var ShoppingBasket\Items\Item[] $items */
        $items = $basket->getItems()->getItems();

        self::assertCount(2, $items);

        self::assertEquals('item-sku-1', $items[0]->getArticleNumber());
        self::assertEquals(3, $items[0]->getQuantity());

        self::assertEquals('item-sku-2', $items[1]->getArticleNumber());
        self::assertEquals(2, $items[1]->getQuantity());
    }

    /**
     * @depends testPartial
     */
    public function testDiscountItemInBasket()
    {
        $factory = $this->getFactory();

        $requestData = $this->createRequestData(['discount-id-1' => 1, 'discount-id-2' => 1]);
        /** @var RatepayOrderDataEntity $ratepayData */
        $ratepayData = $requestData->getOrder()->getExtension(OrderExtension::EXTENSION_NAME);
        $ratepayData->__set(RatepayOrderDataEntity::FIELD_SEND_DISCOUNT_AS_CART_ITEM, true);

        $basket = $factory->getData($requestData);
        /** @var ShoppingBasket\Items\Item[] $items */
        $items = $basket->getItems()->getItems();

        self::assertCount(2, $items);

        self::assertEquals('Discount No 1', $items[0]->getDescription());
        self::assertEquals('discount-id-1', $items[0]->getArticleNumber());
        self::assertEquals(-10.0, $items[0]->getUnitPriceGross());

        self::assertEquals('Discount No 2', $items[1]->getDescription());
        self::assertEquals('discount-id-2', $items[1]->getArticleNumber());
        self::assertEquals(-3.0, $items[1]->getUnitPriceGross());
    }

    /**
     * @depends testPartial
     */
    public function testShippingItemInBasket()
    {
        $factory = $this->getFactory();

        $requestData = $this->createRequestData(['shipping' => 1]);
        $requestData->getOrder()->setLineItems(new OrderLineItemCollection([]));
        /** @var RatepayOrderDataEntity $ratepayData */
        $ratepayData = $requestData->getOrder()->getExtension(OrderExtension::EXTENSION_NAME);
        $ratepayData->__set(RatepayOrderDataEntity::FIELD_SEND_SHIPPING_COSTS_AS_CART_ITEM, true);

        $basket = $factory->getData($requestData);
        /** @var ShoppingBasket\Items\Item[] $items */
        $items = $basket->getItems()->getItems();

        self::assertCount(1, $items);

        self::assertNotNull($items[0]->getDescription());
        self::assertEquals('shipping', $items[0]->getArticleNumber());
        self::assertEquals(4.90, $items[0]->getUnitPriceGross());
    }

    private function createRequestData(array $operationItems = null): OrderOperationData
    {
        $order = OrderMock::createMock();

        if (!$operationItems) {
            $operationItems = [];
            foreach ($order->getLineItems() as $item) {
                $operationItems[$item->getId()] = $item->getQuantity();
            }
            $operationItems['shipping'] = 1;
        }

        return new OrderOperationData(Context::createDefaultContext(), $order, '', $operationItems);
    }

    private function getFactory(): ShoppingBasketFactory
    {
        return new ShoppingBasketFactory(new EventDispatcher());
    }
}
