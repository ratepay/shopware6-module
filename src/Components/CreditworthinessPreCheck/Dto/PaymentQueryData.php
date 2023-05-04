<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto;

use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\CheckoutOperationInterface;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OperationDataWithBasket;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentQueryData extends AbstractRequestData implements OperationDataWithBasket, CheckoutOperationInterface
{
    /**
     * @var LineItem[]
     */
    private array $items = [];

    public function __construct(
        private readonly SalesChannelContext $salesChannelContext,
        private readonly Cart $cart,
        private readonly DataBag $requestDataBag,
        private readonly ?string $transactionId,
        private readonly bool $sendDiscountAsCartItem = false,
        private readonly bool $sendShippingCostsAsCartItem = false
    ) {
        parent::__construct($salesChannelContext->getContext());
    }

    public function getItems(): array
    {
        if ($this->items) {
            return $this->items;
        }

        $items = [];
        foreach ($this->cart->getLineItems() as $item) {
            $items[$item->getId()] = $item;
        }

        if ($this->cart->getShippingCosts()->getTotalPrice() > 0) {
            $items[OrderOperationData::ITEM_ID_SHIPPING] = 1;
        }

        return $items;
    }

    public function getRequestDataBag(): DataBag
    {
        return $this->requestDataBag;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function getCurrencyCode(): string
    {
        return $this->salesChannelContext->getCurrency()->getIsoCode();
    }

    public function getShippingCosts(): ?CalculatedPrice
    {
        return $this->cart->getShippingCosts();
    }

    public function isSendDiscountAsCartItem(): bool
    {
        return $this->sendDiscountAsCartItem;
    }

    public function isSendShippingCostsAsCartItem(): bool
    {
        return $this->sendShippingCostsAsCartItem;
    }

    public function getPaymentMethodId(): string
    {
        return $this->getSalesChannelContext()->getPaymentMethod()->getId();
    }

    public function getCustomer(): ?CustomerEntity
    {
        return $this->getSalesChannelContext()->getCustomer();
    }
}
