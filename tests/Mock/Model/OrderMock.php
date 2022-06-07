<?php

namespace Ratepay\RpayPayments\Tests\Mock\Model;

use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Uuid\Uuid;

class OrderMock
{

    public static function createMock(string $paymentMethodHandler = InvoicePaymentHandler::class)
    {
        $country = CountryMock::createMock('DE');

        $order = new OrderEntity();
        $order->setId('3ad87271180043478d4941bfdf675fca');
        $order->setVersionId(Uuid::randomHex());
        $order->setLineItems(new OrderLineItemCollection());
        $order->setCurrency(CurrencyMock::createMock('EUR'));

        // set sales channel
        $order->setSalesChannel(SalesChannelMock::createMock());
        $order->setSalesChannelId($order->getSalesChannel()->getId());

        // set billing address
        $billingAddress = OrderAddressMock::createAddressEntity($order, false, $country);
        $order->setBillingAddress($billingAddress);
        $order->setBillingAddressId($order->getBillingAddress()->getId());

        // set shipping address
        $shippingAddress = OrderAddressMock::createAddressEntity($order, false, $country);
        $orderDelivery = new OrderDeliveryEntity();
        $orderDelivery->setId(Uuid::randomHex());
        $orderDelivery->setShippingOrderAddress($shippingAddress);
        $orderDelivery->setShippingOrderAddressId($orderDelivery->getShippingOrderAddress()->getId());
        $order->setDeliveries(new OrderDeliveryCollection([$orderDelivery->getId() => $orderDelivery]));

        // set address collection
        $order->setAddresses(new OrderAddressCollection([
            $billingAddress->getId() => $billingAddress,
            $shippingAddress->getId() => $shippingAddress
        ]));

        // set transaction
        $transaction = new OrderTransactionEntity();
        $transaction->setId(Uuid::randomHex());
        $transaction->setPaymentMethod(PaymentMethodMock::createMock($paymentMethodHandler));
        $transaction->setPaymentMethodId($transaction->getPaymentMethod()->getId());
        $order->setTransactions(new OrderTransactionCollection([$transaction->getId() => $transaction]));

        // set ratepay order data
        $ratepayExtension = new RatepayOrderDataEntity();
        $ratepayExtension->__set(RatepayOrderDataEntity::FIELD_PROFILE_ID, 'profile-id');
        $ratepayExtension->__set(RatepayOrderDataEntity::FIELD_DESCRIPTOR, 'descriptor');
        $ratepayExtension->__set(RatepayOrderDataEntity::FIELD_ORDER, $order);
        $ratepayExtension->__set(RatepayOrderDataEntity::FIELD_ORDER_ID, $order->getId());
        $ratepayExtension->__set(RatepayOrderDataEntity::FIELD_ORDER_VERSION_ID, $order->getVersionId());
        $ratepayExtension->__set(RatepayOrderDataEntity::FIELD_SEND_DISCOUNT_AS_CART_ITEM, false);
        $ratepayExtension->__set(RatepayOrderDataEntity::FIELD_SEND_SHIPPING_COSTS_AS_CART_ITEM, false);
        $ratepayExtension->__set(RatepayOrderDataEntity::FIELD_TRANSACTION_ID, 'transaction-id');
        $ratepayExtension->__set(RatepayOrderDataEntity::FIELD_SUCCESSFUL, true);
        $order->addExtension(OrderExtension::EXTENSION_NAME, $ratepayExtension);

        // set shipping
        $shippingCosts = new CalculatedPrice(
            4.90,
            4.90,
            new CalculatedTaxCollection([
                new CalculatedTax(0, 19, 0),
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
                new CalculatedTax(0, 19, 0),
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
                new CalculatedTax(0, 7, 0),
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
                new CalculatedTax(0, 19, 0),
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
                new CalculatedTax(0, 19, 0),
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
                new TaxRule(19),
            ]),
            $orderLineItem5->getQuantity(),
        ));

        // add debit to oder (after the oder has been completed)
        $orderLineItem6 = new LineItem('394a735223dc482f8e06a6924f475632', '', '', 1);
        $orderLineItem6->setLabel('Debit No 1');
        $orderLineItem6->setPriceDefinition(new QuantityPriceDefinition(
            15,
            new TaxRuleCollection([
                new TaxRule(19),
            ]),
            $orderLineItem6->getQuantity()
        ));

        return $order;
    }
}
