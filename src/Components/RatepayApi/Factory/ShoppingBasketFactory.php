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
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentQueryData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Exception\EmptyBasketException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;

/**
 * @method getData(PaymentRequestData|PaymentQueryData|OrderOperationData $requestData) : ?Head
 */
class ShoppingBasketFactory extends AbstractFactory
{
    protected function isSupported(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof OrderOperationData || $requestData instanceof PaymentQueryData;
    }

    protected function _getData(AbstractRequestData $requestData): ?object
    {
        if ($requestData instanceof OrderOperationData) {
            $order = $requestData->getOrder();
            $shippingCosts = $order->getShippingCosts();
            $currency = $order->getCurrency()->getIsoCode();
        } elseif ($requestData instanceof PaymentQueryData) {
            $shippingCosts = $requestData->getCart()->getShippingCosts();
            $currency = $requestData->getSalesChannelContext()->getCurrency()->getIsoCode();
        } else {
            return null;
        }

        $basket = new ShoppingBasket();
        $basket->setItems(new ShoppingBasket\Items());
        $basket->setCurrency($currency);

        $items = $requestData->getItems();
        if (count($items) === 0) {
            throw new EmptyBasketException();
        }

        foreach ($items as $id => $qty) {
            if ($qty instanceof LineItem) {
                /** @var LineItem $item */
                $item = $qty;
                /** @var PriceDefinitionInterface $priceDefinition */
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
                if ($shippingCosts->getTotalPrice()) {
                    $basket->setShipping(
                        (new ShoppingBasket\Shipping())
                            ->setDescription('shipping')
                            ->setUnitPriceGross($shippingCosts->getTotalPrice())
                            ->setTaxRate($this->getTaxRate($shippingCosts))
                    );
                }
            } elseif ($requestData instanceof OrderOperationData) {
                $order = $requestData->getOrder();
                $item = $order->getLineItems()->get($id);
                if (!$item) {
                    throw new InvalidArgumentException($id . ' does not belongs to the order ' . $order->getId());
                }
                $this->addOrderLineItemToBasket($basket, $item, $qty);
            }
        }

        return $basket;
    }

    private function getTaxRate(CalculatedPrice $calculatedPrice): float
    {
        $tax = $calculatedPrice->getCalculatedTaxes()->first();

        return $tax ? $tax->getTaxRate() : 0;
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
}
