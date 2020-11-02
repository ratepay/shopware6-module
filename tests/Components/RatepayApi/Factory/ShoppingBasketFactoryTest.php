<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;


use PHPUnit\Framework\TestCase;
use RatePAY\Model\Request\SubModel\Content\ShoppingBasket;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
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
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\System\Currency\CurrencyEntity;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class ShoppingBasketFactoryTest extends TestCase
{

    use KernelTestBehaviour;


    public function testGetData()
    {
        $shoppingBasketFactory = new ShoppingBasketFactory(new EventDispatcher(), new RequestStack());

        $requestData = $this->createRequestData();
        /** @var ShoppingBasket $shoppingBasket */
        $shoppingBasket = $shoppingBasketFactory->getData($requestData);


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

        $requestData = new OrderOperationData($requestData->getOrder(), $requestData->getOperation(), array_merge(
            $requestData->getItems(),
            [
                'unknown-id' => 'unknown-id'
            ]
        ), false);
        $shoppingBasketFactory->getData($requestData);

    }


    private function createRequestData()
    {

        $order = new OrderEntity();
        $order->setId('3ad87271180043478d4941bfdf675fca');
        $order->setLineItems(new OrderLineItemCollection());

        // set currency
        $currency = new CurrencyEntity();
        $currency->setIsoCode('EUR');
        $order->setCurrency($currency);

        // set shipping
        $shippingCosts = new CalculatedPrice(
            4.90,
            4.90,
            new CalculatedTaxCollection([
                new CalculatedTax(0, 19, 0)
            ]),
            new TaxRuleCollection([]),
            1
        );
        $order->setShippingCosts($shippingCosts);

        // add line item with 19 % tax
        $orderLineItem1 = new OrderLineItemEntity();
        $orderLineItem1->setId('de00edc736b2465f90e58577ee8afa11');
        $orderLineItem1->setPayload(['productNumber' => '88884444455555']);
        $orderLineItem1->setType(LineItem::PRODUCT_LINE_ITEM_TYPE);
        $orderLineItem1->setLabel('Product No 1');
        $orderLineItem1->setQuantity(3);
        $orderLineItem1->setPrice(new CalculatedPrice(
            30,
            30 * $orderLineItem1->getQuantity(),
            new CalculatedTaxCollection([
                new CalculatedTax(0, 19, 0)
            ]),
            new TaxRuleCollection([]),
            $orderLineItem1->getQuantity()
        ));
        $orderLineItem1->setTotalPrice($orderLineItem1->getPrice()->getTotalPrice());
        $orderLineItem1->setOrder($order);
        $orderLineItem1->setOrderId($order->getId());
        $order->getLineItems()->add($orderLineItem1);

        // add line item with 7 % tax
        $orderLineItem2 = new OrderLineItemEntity();
        $orderLineItem2->setId('2d3e36c231cb457a8f0684fbc1aad4c0');
        $orderLineItem2->setPayload(['productNumber' => '44444333332222']);
        $orderLineItem2->setType(LineItem::PRODUCT_LINE_ITEM_TYPE);
        $orderLineItem2->setLabel('Product No 2');
        $orderLineItem2->setQuantity(10);
        $orderLineItem2->setPrice(new CalculatedPrice(
            5,
            5 * $orderLineItem2->getQuantity(),
            new CalculatedTaxCollection([
                new CalculatedTax(0, 7, 0)
            ]),
            new TaxRuleCollection([]),
            $orderLineItem2->getQuantity()
        ));
        $orderLineItem2->setTotalPrice($orderLineItem2->getPrice()->getTotalPrice());
        $orderLineItem2->setOrder($order);
        $orderLineItem2->setOrderId($order->getId());
        $order->getLineItems()->add($orderLineItem2);

        // add line item with (discount)
        $orderLineItem3 = new OrderLineItemEntity();
        $orderLineItem3->setId('316c5af5007241298849ac60a47eca61');
        $orderLineItem3->setLabel('Discount No 1');
        $orderLineItem3->setQuantity(1);
        $orderLineItem3->setPrice(new CalculatedPrice(
            -10,
            -10 * $orderLineItem3->getQuantity(),
            new CalculatedTaxCollection([
                new CalculatedTax(0, 19, 0)
            ]),
            new TaxRuleCollection([]),
            $orderLineItem3->getQuantity()
        ));
        $orderLineItem3->setTotalPrice($orderLineItem3->getPrice()->getTotalPrice());
        $orderLineItem3->setOrder($order);
        $orderLineItem3->setOrderId($order->getId());
        $order->getLineItems()->add($orderLineItem3);

        // add line item with (second discount)
        $orderLineItem4 = new OrderLineItemEntity();
        $orderLineItem4->setId('48524b7e247342f392181a0cfbe86743');
        $orderLineItem4->setLabel('Discount No 2');
        $orderLineItem4->setQuantity(1);
        $orderLineItem4->setPrice(new CalculatedPrice(
            -3,
            -3 * $orderLineItem4->getQuantity(),
            new CalculatedTaxCollection([
                new CalculatedTax(0, 19, 0)
            ]),
            new TaxRuleCollection([]),
            $orderLineItem4->getQuantity()
        ));
        $orderLineItem4->setTotalPrice($orderLineItem4->getPrice()->getTotalPrice());
        $orderLineItem4->setOrder($order);
        $orderLineItem4->setOrderId($order->getId());
        $order->getLineItems()->add($orderLineItem4);

        // add credit to oder (after the oder has been completed)
        $orderLineItem5 = new LineItem('c71e96be4dcc46cfae1495aa33afe328', '', '', 1);
        $orderLineItem5->setLabel('Credit No 1');
        $orderLineItem5->setPriceDefinition(new QuantityPriceDefinition(
            -15,
            new TaxRuleCollection([
                new TaxRule(19)
            ]),
            4,
            $orderLineItem5->getQuantity(),
        ));

        // add debit to oder (after the oder has been completed)
        $orderLineItem6 = new LineItem('394a735223dc482f8e06a6924f475632', '', '', 1);
        $orderLineItem6->setLabel('Debit No 1');
        $orderLineItem6->setPriceDefinition(new QuantityPriceDefinition(
            15,
            new TaxRuleCollection([
                new TaxRule(19)
            ]),
            4,
            $orderLineItem6->getQuantity()
        ));

        return new OrderOperationData(
            $order,
            '',
            [
                $orderLineItem1->getId() => $orderLineItem1->getQuantity(),
                $orderLineItem2->getId() => $orderLineItem2->getQuantity(),
                $orderLineItem3->getId() => $orderLineItem3->getQuantity(),
                $orderLineItem4->getId() => $orderLineItem4->getQuantity(),
                $orderLineItem5->getId() => $orderLineItem5,
                $orderLineItem6->getId() => $orderLineItem6,
                'shipping' => 'shipping'
            ],
            false,
        );
    }
}
