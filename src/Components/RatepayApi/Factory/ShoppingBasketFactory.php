<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use BadMethodCallException;
use InvalidArgumentException;
use RatePAY\Model\Request\SubModel\Content\ShoppingBasket;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentQueryData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OperationDataWithBasket;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Exception\EmptyBasketException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;

/**
 * @method getData(PaymentRequestData|PaymentQueryData|OrderOperationData $requestData) : ?Head
 */
class ShoppingBasketFactory extends AbstractFactory
{

    protected function isSupported(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof OperationDataWithBasket;
    }

    protected function _getData(AbstractRequestData $requestData): ?object
    {
        /** @var OperationDataWithBasket $requestData */

        $shippingCosts = $requestData->getShippingCosts();
        $currency = $requestData->getCurrencyCode();

        $basket = new ShoppingBasket();
        $basket->setItems(new ShoppingBasket\Items());
        $basket->setCurrency($currency);

        $items = $requestData->getItems();
        if (count($items) === 0) {
            throw new EmptyBasketException();
        }

        foreach ($items as $id => $qty) {
            if ($qty instanceof LineItem) {
                $this->addOrderLineItemToBasketByBasketLineItem($requestData, $basket, $qty);
            } elseif ($id === OrderOperationData::ITEM_ID_SHIPPING && $shippingCosts) {
                $this->addShippingCosts($requestData, $basket, $shippingCosts);
            } elseif ($requestData instanceof OrderOperationData) {
                $order = $requestData->getOrder();
                $item = $order->getLineItems()->get($id);
                if (!$item) {
                    throw new InvalidArgumentException($id . ' does not belongs to the order ' . $order->getId());
                }
                $this->addOrderLineItemToBasketByOrderItem($requestData, $basket, $item, $qty);
            }
        }

        return $basket;
    }

    private function getTaxRate(CalculatedPrice $calculatedPrice): float
    {
        $tax = $calculatedPrice->getCalculatedTaxes()->first();

        return $tax ? $tax->getTaxRate() : 0;
    }

    protected function addShippingCosts(OperationDataWithBasket $requestData, ShoppingBasket $basket, CalculatedPrice $shippingCosts): void
    {
        if ($shippingCosts->getTotalPrice() <= 0) {
            return;
        }

        $taxStatus = $this->getTaxStatus($requestData);

        if ($requestData->isSendShippingCostsAsCartItem()) {
            $basket->getItems()->addItem(
                (new ShoppingBasket\Items\Item())
                    ->setArticleNumber(OrderOperationData::ITEM_ID_SHIPPING)
                    ->setDescription(OrderOperationData::ITEM_ID_SHIPPING) // TODO is it an idea to change it to method name?
                    ->setQuantity(1)
                    ->setUnitPriceGross($this->getLineItemUnitPrice($taxStatus, $shippingCosts, 1))
                    ->setTaxRate($this->getTaxRate($shippingCosts))
            );
        } else {
            $basket->setShipping(
                (new ShoppingBasket\Shipping())
                    ->setDescription('shipping')
                    ->setUnitPriceGross($this->getLineItemUnitPrice($taxStatus, $shippingCosts, 1))
                    ->setTaxRate($this->getTaxRate($shippingCosts))
            );
        }
    }

    protected function addOrderLineItemToBasketByOrderItem(
        OperationDataWithBasket $requestData,
        ShoppingBasket $basket,
        OrderLineItemEntity $item,
        $qty
    ): void
    {
        $taxStatus = $this->getTaxStatus($requestData);

        if ($this->shouldSubmitItemAsCartItem($requestData, $item, $item->getTotalPrice())) {
            $basket->getItems()->addItem(
                (new ShoppingBasket\Items\Item())
                    ->setArticleNumber($item->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE ? $item->getPayload()['productNumber'] : $item->getIdentifier())
                    ->setDescription($item->getLabel())
                    ->setQuantity($qty)
                    ->setUnitPriceGross($this->getLineItemUnitPrice($taxStatus, $item->getPrice(), $qty))
                    ->setTaxRate($this->getTaxRate($item->getPrice()))
            );
        } else {
            $discount = $basket->getDiscount() ?: new ShoppingBasket\Discount();
            $discount->setDescription('discount');
            $discount->setDescriptionAddition(($discount->getDescriptionAddition() ? $discount->getDescriptionAddition() . ', ' : null) . $item->getLabel());
            $discount->setUnitPriceGross($discount->getUnitPriceGross() + $this->getLineItemUnitPrice($taxStatus, $item->getPrice(), $qty));
            $discount->setTaxRate($this->getTaxRate($item->getPrice()));
            $basket->setDiscount($discount);
        }
    }

    protected function addOrderLineItemToBasketByBasketLineItem(OperationDataWithBasket $requestData, ShoppingBasket $basket, LineItem $item): void
    {
        $taxStatus = $this->getTaxStatus($requestData);

        $unitPrice = $item->getPrice() ? $this->getLineItemUnitPrice($taxStatus, $item->getPrice(), $item->getQuantity()) : 0;

        if ($this->shouldSubmitItemAsCartItem($requestData, $item, $unitPrice)) {
            $basket->getItems()->addItem(
                (new ShoppingBasket\Items\Item())
                    ->setArticleNumber($item->getId())
                    ->setDescription($item->getLabel())
                    ->setQuantity($item->getQuantity())
                    ->setUnitPriceGross($unitPrice)
                    ->setTaxRate($this->getTaxRate($item->getPrice()))
            );
        } else {
            $discount = $basket->getDiscount() ?: new ShoppingBasket\Discount();
            $discount->setDescription('discount');
            $discount->setDescriptionAddition(($discount->getDescriptionAddition() ? $discount->getDescriptionAddition() . ', ' : null) . $item->getLabel());
            $discount->setUnitPriceGross($discount->getUnitPriceGross() + $unitPrice);
            $discount->setTaxRate($this->getTaxRate($item->getPrice()));
            $basket->setDiscount($discount);
        }
    }

    /**
     * @param OperationDataWithBasket $requestData
     * @param LineItem|OrderLineItemEntity $item
     * @param float $price
     * @return bool
     */
    protected function shouldSubmitItemAsCartItem(OperationDataWithBasket $requestData, $item, float $price): bool
    {
        if ($price > 0 || !in_array($item->getType(), [LineItem::CREDIT_LINE_ITEM_TYPE, LineItem::PROMOTION_LINE_ITEM_TYPE], true)) {
            return true;
        }

        return $requestData->isSendDiscountAsCartItem();
    }

    private function getTaxStatus(OperationDataWithBasket $requestData): string
    {
        if ($requestData instanceof OrderOperationData) {
            return $requestData->getOrder()->getTaxStatus();
        }

        if ($requestData instanceof PaymentQueryData) {
            return $requestData->getCart()->getPrice()->getTaxStatus();
        }

        throw new BadMethodCallException(get_class($requestData) . ' is not implemented yet');
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
