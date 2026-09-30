<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use RatePAY\Model\Request\SubModel\Content\ShoppingBasket;
use RatePAY\Model\Request\SubModel\Content\ShoppingBasket\Discount;
use RatePAY\Model\Request\SubModel\Content\ShoppingBasket\Items;
use RatePAY\Model\Request\SubModel\Content\ShoppingBasket\Items\Item;
use RatePAY\Model\Request\SubModel\Content\ShoppingBasket\Shipping;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OperationDataWithCart;
use Ratepay\RpayPayments\Components\RatepayApi\Exception\EmptyBasketException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTax;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;

/**
 * @extends AbstractFactory<ShoppingBasket>
 */
class CartShoppingBasketFactory extends AbstractFactory
{
    protected function isSupported(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof OperationDataWithCart;
    }

    protected function _getData(AbstractRequestData $requestData): ?object
    {
        /** @var OperationDataWithCart $requestData */

        $basket = new ShoppingBasket();
        $basket->setAmount($requestData->getCart()->getPrice()->getTotalPrice());
        $basket->setCurrency($requestData->getCurrency()->getIsoCode());
        $basket->setItems(new Items());

        if ($requestData->getCart()->getLineItems()->isEmpty() === []) {
            throw new EmptyBasketException();
        }

        foreach ($requestData->getCart()->getLineItems()->getElements() as $lineItem) {
            $this->addOrderLineItemToBasketByBasketLineItem($requestData, $basket, $lineItem);
        }
        $this->addShippingCosts($requestData, $basket);

        return $basket;
    }

    protected function addOrderLineItemToBasketByBasketLineItem(OperationDataWithCart $requestData, ShoppingBasket $basket, LineItem $item): void
    {
        $taxStatus = $requestData->getTaxState();

        $unitPrice = $item->getPrice() instanceof CalculatedPrice ? $this->getLineItemUnitPrice($taxStatus, $item->getPrice(), $item->getQuantity()) : 0;

        if ($this->shouldSubmitItemAsCartItem($item, $unitPrice)) {
            $basket->getItems()->addItem(
                (new Item())
                    ->setArticleNumber($item->getId())
                    ->setDescription($item->getLabel())
                    ->setQuantity($item->getQuantity())
                    ->setUnitPriceGross($unitPrice)
                    ->setTaxRate($this->getTaxRate($item->getPrice()))
            );
        } else {
            /* @phpstan-ignore-next-line */
            $discount = $basket->getDiscount() ?: new Discount();
            $discount->setDescription('discount');
            $discount->setDescriptionAddition((!empty($discount->getDescriptionAddition()) ? $discount->getDescriptionAddition() . ', ' : null) . $item->getLabel());
            $discount->setUnitPriceGross($discount->getUnitPriceGross() + $unitPrice);
            $discount->setTaxRate($this->getTaxRate($item->getPrice()));
            $basket->setDiscount($discount);
        }
    }

    protected function addShippingCosts(OperationDataWithCart $requestData, ShoppingBasket $basket): void
    {
        $shippingCosts = $requestData->getCart()->getShippingCosts();
        if ($shippingCosts->getTotalPrice() <= 0) {
            return;
        }

        $taxStatus = $requestData->getTaxState();

        $basket->setShipping(
            (new Shipping())
                ->setDescription('shipping')
                ->setUnitPriceGross($this->getLineItemUnitPrice($taxStatus, $shippingCosts, 1))
                ->setTaxRate($this->getTaxRate($shippingCosts))
        );
    }

    protected function shouldSubmitItemAsCartItem(LineItem|OrderLineItemEntity $item, float $price): bool
    {
        return $price > 0 || !in_array($item->getType(), [LineItem::CREDIT_LINE_ITEM_TYPE, LineItem::PROMOTION_LINE_ITEM_TYPE], true);
    }

    private function getTaxRate(CalculatedPrice $calculatedPrice): float
    {
        $tax = $calculatedPrice->getCalculatedTaxes()->first();

        return $tax instanceof CalculatedTax ? $tax->getTaxRate() : 0;
    }

    private function getLineItemUnitPrice(string $taxStatus, CalculatedPrice $price, int $qty): float
    {
        $unitPrice = $price->getUnitPrice();

        // RATESWSX-211: Shopware does not store the gross prices für line-items into the database.
        // only the net prices. So we must re-add the tax to the unit price.
        if ($taxStatus === CartPrice::TAX_STATE_NET) {
            foreach ($price->getCalculatedTaxes() as $tax) {
                $unitPrice += $tax->getTax() / $qty;
            }
        }

        return $unitPrice;
    }
}
