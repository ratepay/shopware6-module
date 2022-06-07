<?php

namespace Ratepay\RpayPayments\Tests\Mock\Model;

use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
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
        $order->setTaxStatus(CartPrice::TAX_STATE_GROSS);

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

        $items = [
            ['id' => 1, 'qty' => 3, 'price' => 30, 'tax' => 19],
            ['id' => 2, 'qty' => 10, 'price' => 5, 'tax' => 7],
            ['id' => 3, 'qty' => 6, 'price' => 50, 'tax' => 19],
        ];

        foreach ($items as $itemData) {
            // add line item with 19 % tax
            $orderLineItem = new OrderLineItemEntity();
            $orderLineItem->setId('item-id-' . $itemData['id']);
            $orderLineItem->setPayload(['productNumber' => 'item-sku-' . $itemData['id']]);
            $orderLineItem->setLabel('Product No ' . $itemData['id']);
            $orderLineItem->setType(LineItem::PRODUCT_LINE_ITEM_TYPE);
            $orderLineItem->setQuantity($itemData['qty']);
            $orderLineItem->setPrice(new CalculatedPrice(
                $itemData['price'],
                $itemData['price'] * $orderLineItem->getQuantity(),
                new CalculatedTaxCollection([
                    new CalculatedTax(0, $itemData['tax'], 0),
                ]),
                new TaxRuleCollection([]),
                $orderLineItem->getQuantity()
            ));
            $orderLineItem->setTotalPrice($orderLineItem->getPrice()->getTotalPrice());
            $orderLineItem->setOrder($order);
            $orderLineItem->setOrderId($order->getId());
            $order->getLineItems()->add($orderLineItem);
        }

        // add line item with discount
        $orderLineItem3 = new OrderLineItemEntity();
        $orderLineItem3->setId('discount-id-1');
        $orderLineItem3->setLabel('Discount No 1');
        $orderLineItem3->setType(LineItem::CREDIT_LINE_ITEM_TYPE);
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

        // add line item second discount
        $orderLineItem4 = new OrderLineItemEntity();
        $orderLineItem4->setId('discount-id-2');
        $orderLineItem4->setLabel('Discount No 2');
        $orderLineItem4->setType(LineItem::PROMOTION_LINE_ITEM_TYPE);
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

        //// add credit to oder (after the oder has been completed)
        //$orderLineItem5 = new LineItem('credit-id-1', '', '', 1);
        //$orderLineItem5->setLabel('Credit No 1');
        //$orderLineItem5->setPriceDefinition(new QuantityPriceDefinition(
        //    -15,
        //    new TaxRuleCollection([
        //        new TaxRule(19),
        //    ]),
        //    $orderLineItem5->getQuantity(),
        //));
        //
        //// add debit to oder (after the oder has been completed)
        //$orderLineItem6 = new LineItem('394a735223dc482f8e06a6924f475632', '', '', 1);
        //$orderLineItem6->setLabel('Debit No 1');
        //$orderLineItem6->setPriceDefinition(new QuantityPriceDefinition(
        //    15,
        //    new TaxRuleCollection([
        //        new TaxRule(19),
        //    ]),
        //    $orderLineItem6->getQuantity()
        //));

        foreach ($order->getLineItems() as $item) {
            $item->setIdentifier($item->getId());
        }

        return $order;
    }
}
