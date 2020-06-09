<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Factory;


use RatePAY\Model\Request\SubModel\Content\ShoppingBasket;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\OrderEntity;

class ShoppingBasketFactory
{


    public function getData(OrderEntity $order, array $itemsToSend = [])
    {
        $sendAll = count($itemsToSend) === 0;

        $basket = new ShoppingBasket();
        $basket->setItems(new ShoppingBasket\Items());

        /** @var OrderLineItemCollection $lineItems */
        $lineItems = $order->getLineItems();
        $lineItems = $sendAll ? $lineItems : $lineItems->getList(array_keys($itemsToSend));
        foreach ($lineItems as $item) {
            if ($item->getTotalPrice() > 0) {
                $basket->getItems()->addItem(
                    (new ShoppingBasket\Items\Item())
                        ->setArticleNumber($item->getPayload()['productNumber'])
                        ->setDescription($item->getLabel())
                        ->setQuantity($sendAll ? $item->getQuantity() : $itemsToSend[$item->getId()])
                        ->setUnitPriceGross($item->getPrice()->getUnitPrice())
                        ->setTaxRate($item->getPrice()->getCalculatedTaxes()->first()->getTaxRate())
                );
            } else {
                $discount = $basket->getDiscount() ?: new ShoppingBasket\Discount();
                $discount->setDescription($discount->getDescription() ? $discount->getDescription() . ', ' : $item->getLabel());
                $discount->setUnitPriceGross($discount->getUnitPriceGross() + $item->getTotalPrice());
                $basket->setDiscount($discount);
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

}
