<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Factory;


use InvalidArgumentException;
use RatePAY\Model\Request\SubModel\Content\ShoppingBasket;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;

class ShoppingBasketFactory
{


    public function getData(OrderEntity $order, array $itemsToSend = [])
    {
        $sendAll = count($itemsToSend) === 0;

        $basket = new ShoppingBasket();
        $basket->setItems(new ShoppingBasket\Items());

        //$lineItems = $sendAll ? $lineItems : $lineItems->getList(array_keys($itemsToSend));
        foreach ($itemsToSend as $id => $qty) {
            if ($qty instanceof LineItem) {
                // this is a credit or a debit after the order has been placed

                /** @var LineItem $item */
                $item = $qty;
                /** @var QuantityPriceDefinition $priceDefinition */
                $priceDefinition = $item->getPriceDefinition();
                $basket->getItems()->addItem(
                    (new ShoppingBasket\Items\Item())
                        ->setArticleNumber($item->getId())
                        ->setDescription($item->getLabel())
                        ->setQuantity($priceDefinition->getQuantity())
                        ->setUnitPriceGross($priceDefinition->getPrice())
                        ->setTaxRate($priceDefinition->getTaxRules()->first()->getTaxRate())
                );
            } else {
                $item = $order->getLineItems()->get($id);
                if (!$item) {
                    throw new InvalidArgumentException($id . ' does not belongs to the order ' . $order->getId());
                }
                $this->addOrderLineItemToBasket($basket, $item, $qty);
            }
        }
        if (count($itemsToSend) === 0) {
            // send all items
            foreach ($order->getLineItems() as $item) {
                $this->addOrderLineItemToBasket($basket, $item, $item->getQuantity());
            }
        }

        if (($sendAll || isset($itemsToSend['shipping'])) && $order->getShippingCosts()->getTotalPrice()) {
            $basket->setShipping(
                (new ShoppingBasket\Shipping())
                    ->setDescription('shipping')
                    ->setUnitPriceGross($order->getShippingCosts()->getTotalPrice())
                    ->setTaxRate($order->getShippingCosts()->getCalculatedTaxes()->first()->getTaxRate())
            );
        }

        return $basket;
    }

    protected function addOrderLineItemToBasket(ShoppingBasket $basket, OrderLineItemEntity $item, $qty)
    {
        if ($item->getTotalPrice() > 0) {
            $basket->getItems()->addItem(
                (new ShoppingBasket\Items\Item())
                    ->setArticleNumber($item->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE ? $item->getPayload()['productNumber'] : $item->getIdentifier())
                    ->setDescription($item->getLabel())
                    ->setQuantity($qty)
                    ->setUnitPriceGross($item->getPrice()->getUnitPrice())
                    ->setTaxRate($item->getPrice()->getCalculatedTaxes()->first()->getTaxRate())
            );
        } else {
            $discount = $basket->getDiscount() ?: new ShoppingBasket\Discount();
            $discount->setDescription('discount');
            $discount->setDescriptionAddition($discount->getDescription() ? $discount->getDescription() . ', ' : $item->getLabel());
            $discount->setUnitPriceGross($discount->getUnitPriceGross() + $item->getTotalPrice());
            $discount->setTaxRate($item->getPrice()->getCalculatedTaxes()->first()->getTaxRate());
            $basket->setDiscount($discount);
        }
    }

}
