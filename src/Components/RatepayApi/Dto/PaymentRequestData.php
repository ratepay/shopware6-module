<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentRequestData extends OrderOperationData implements CheckoutOperationInterface
{
    private DataBag $requestDataBag;

    private SalesChannelContext $salesChannelContext;

    private ?string $ratepayTransactionId;

    private bool $sendDiscountAsCartItem;

    private bool $sendShippingCostsAsCartItem;

    public function __construct(
        SalesChannelContext $salesChannelContext,
        OrderEntity $order,
        OrderTransactionEntity $transaction,
        DataBag $requestDataBag,
        string $ratepayTransactionId = null,
        bool $sendDiscountAsCartItem = false,
        bool $sendShippingCostsAsCartItem = false
    )
    {
        parent::__construct($salesChannelContext->getContext(), $order, self::OPERATION_REQUEST, null, false);
        $this->transaction = $transaction;
        $this->requestDataBag = $requestDataBag;
        $this->salesChannelContext = $salesChannelContext;
        $this->ratepayTransactionId = $ratepayTransactionId;
        $this->sendDiscountAsCartItem = $sendDiscountAsCartItem;
        $this->sendShippingCostsAsCartItem = $sendShippingCostsAsCartItem;
    }

    /**
     * @return array<int, string>
     */
    public function getItems(): array
    {
        if ($this->items) {
            return $this->items;
        }

        $items = [];
        foreach ($this->getOrder()->getLineItems() as $item) {
            $items[$item->getId()] = $item->getQuantity();
        }

        if ($this->getOrder()->getShippingTotal() > 0) {
            $items[self::ITEM_ID_SHIPPING] = 1;
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

    public function getRatepayTransactionId(): ?string
    {
        return $this->ratepayTransactionId;
    }

    public function setRatepayTransactionId(string $ratepayTransactionId = null): void
    {
        $this->ratepayTransactionId = $ratepayTransactionId;
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
        return $this->getOrder()->getTransactions()->last()->getPaymentMethodId();
    }

    public function getCustomer()
    {
        return $this->getOrder()->getOrderCustomer()->getCustomer();
    }
}
