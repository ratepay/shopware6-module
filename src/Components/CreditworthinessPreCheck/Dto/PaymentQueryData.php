<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto;

use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentQueryData extends AbstractRequestData
{
    /** @var RequestDataBag */
    private $requestDataBag;

    /** @var SalesChannelContext */
    private $salesChannelContext;

    /** @var LineItem[] */
    private $items;

    /** @var string|null */
    private $transactionId;

    /**
     * @var Cart
     */
    private $cart;

    public function __construct(
        SalesChannelContext $salesChannelContext,
        Cart $cart,
        RequestDataBag $requestDataBag,
        string $transactionId
    ) {
        parent::__construct($salesChannelContext->getContext());
        $this->requestDataBag = $requestDataBag;
        $this->salesChannelContext = $salesChannelContext;
        $this->cart = $cart;
        $this->transactionId = $transactionId;
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
            $items['shipping'] = 1;
        }

        return $items;
    }

    public function getRequestDataBag(): RequestDataBag
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
}
