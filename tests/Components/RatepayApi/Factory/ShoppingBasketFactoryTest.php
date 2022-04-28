<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\RatepayApi\Factory;

use PHPUnit\Framework\TestCase;
use RatePAY\Model\Request\SubModel\Content\ShoppingBasket;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Tests\Mock\Model\OrderMock;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Factory\Mock;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
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

class ShoppingBasketFactoryTest extends TestCase
{
    use KernelTestBehaviour;

    public function testGetData()
    {
        $factory = Mock::createShoppingBasketFactory();

        $requestData = $this->createRequestData();
        /** @var ShoppingBasket $shoppingBasket */
        $shoppingBasket = $factory->getData($requestData);

        /** @var ShoppingBasket\Items\Item[] $items */
        $items = $shoppingBasket->getItems()->getItems();
        self::assertCount(4, $items);
        self::assertEquals('88884444455555', $items[0]->getArticleNumber());
        self::assertEquals('Product No 1', $items[0]->getDescription());
        self::assertEquals(3, $items[0]->getQuantity());
        self::assertEquals(30, $items[0]->getUnitPriceGross());
        self::assertEquals(19, $items[0]->getTaxRate());

        self::assertEquals('44444333332222', $items[1]->getArticleNumber());
        self::assertEquals('Product No 2', $items[1]->getDescription());
        self::assertEquals(10, $items[1]->getQuantity());
        self::assertEquals(5, $items[1]->getUnitPriceGross());
        self::assertEquals(7, $items[1]->getTaxRate());

        self::assertEquals('c71e96be4dcc46cfae1495aa33afe328', $items[2]->getArticleNumber());
        self::assertEquals('Credit No 1', $items[2]->getDescription());
        self::assertEquals(1, $items[2]->getQuantity());
        self::assertEquals(-15, $items[2]->getUnitPriceGross());
        self::assertEquals(19, $items[2]->getTaxRate());

        self::assertEquals('394a735223dc482f8e06a6924f475632', $items[3]->getArticleNumber());
        self::assertEquals('Debit No 1', $items[3]->getDescription());
        self::assertEquals(1, $items[3]->getQuantity());
        self::assertEquals(15, $items[3]->getUnitPriceGross());
        self::assertEquals(19, $items[3]->getTaxRate());

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

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('unknown-id does not belongs to the order ' . $requestData->getOrder()->getId());

        $requestData = new OrderOperationData(Context::createDefaultContext(), $requestData->getOrder(), $requestData->getOperation(), array_merge(
            $requestData->getItems(),
            [
                'unknown-id' => 'unknown-id',
            ]
        ), false);
        $factory->getData($requestData);

        // TODO test not finished ....
    }

    private function createRequestData(): OrderOperationData
    {

        $order = OrderMock::createMock();

        $operationItems = [];
        foreach ($order->getLineItems() as $item) {
            $operationItems[$item->getId()] = $item->getId();
        }

        return new OrderOperationData(
            Context::createDefaultContext(),
            $order,
            '',
            array_merge($operationItems, [
                'shipping' => 'shipping',
            ])
        );
    }
}
