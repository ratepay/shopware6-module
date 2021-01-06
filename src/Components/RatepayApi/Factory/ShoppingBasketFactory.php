<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use InvalidArgumentException;
use RatePAY\Model\Request\SubModel\Content\ShoppingBasket;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Exception\EmptyBasketException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;

class ShoppingBasketFactory extends AbstractFactory
{
    protected function _getData(IRequestData $requestData): ?object
    {
        /** @var OrderOperationData $requestData */
        $order = $requestData->getOrder();

        $basket = new ShoppingBasket();
        $basket->setItems(new ShoppingBasket\Items());
        $basket->setCurrency($order->getCurrency()->getIsoCode());

        $items = $requestData->getItems();
        if (count($items) === 0) {
            throw new EmptyBasketException();
        }

        foreach ($items as $id => $qty) {
            if ($qty instanceof LineItem) {
                // this is a credit or a debit after the order has been placed
                /** @var LineItem $item */
                $item = $qty;
                /** @var QuantityPriceDefinition $priceDefinition */
                $priceDefinition = $item->getPriceDefinition();
                if (method_exists($priceDefinition, 'getTaxRules')) {
                    $taxRule = $priceDefinition->getTaxRules()->first();
                    $price = $priceDefinition->getPrice();
                }

                $basket->getItems()->addItem(
                    (new ShoppingBasket\Items\Item())
                        ->setArticleNumber($item->getId())
                        ->setDescription($item->getLabel())
                        ->setQuantity($priceDefinition->getQuantity())
                        ->setUnitPriceGross($item->getPrice() ? $item->getPrice()->getUnitPrice() : (isset($price) ? $price : 0))
                        ->setTaxRate(isset($taxRule) ? $taxRule->getTaxRate() : 0)
                );
            } elseif ($id === 'shipping') {
                if ($order->getShippingCosts()->getTotalPrice()) {
                    $basket->setShipping(
                        (new ShoppingBasket\Shipping())
                            ->setDescription('shipping')
                            ->setUnitPriceGross($order->getShippingCosts()->getTotalPrice())
                            ->setTaxRate($this->getTaxRate($order->getShippingCosts()))
                    );
                }
            } else {
                $item = $order->getLineItems()->get($id);
                if (!$item) {
                    throw new InvalidArgumentException($id . ' does not belongs to the order ' . $order->getId());
                }
                $this->addOrderLineItemToBasket($basket, $item, $qty);
            }
        }

        return $basket;
    }

    protected function addOrderLineItemToBasket(ShoppingBasket $basket, OrderLineItemEntity $item, $qty): void
    {
        if ($item->getTotalPrice() > 0 || $item->getType() === LineItem::CUSTOM_LINE_ITEM_TYPE) {
            $basket->getItems()->addItem(
                (new ShoppingBasket\Items\Item())
                    ->setArticleNumber($item->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE ? $item->getPayload()['productNumber'] : $item->getIdentifier())
                    ->setDescription($item->getLabel())
                    ->setQuantity($qty)
                    ->setUnitPriceGross($item->getPrice()->getUnitPrice())
                    ->setTaxRate($this->getTaxRate($item->getPrice()))
            );
        } else {
            $discount = $basket->getDiscount() ?: new ShoppingBasket\Discount();
            $discount->setDescription('discount');
            $discount->setDescriptionAddition(($discount->getDescriptionAddition() ? $discount->getDescriptionAddition() . ', ' : null) . $item->getLabel());
            $discount->setUnitPriceGross($discount->getUnitPriceGross() + $item->getTotalPrice());
            $discount->setTaxRate($this->getTaxRate($item->getPrice()));
            $basket->setDiscount($discount);
        }
    }

    private function getTaxRate(CalculatedPrice $calculatedPrice): float
    {
        $tax = $calculatedPrice->getCalculatedTaxes()->first();

        return $tax ? $tax->getTaxRate() : 0;
    }
}
