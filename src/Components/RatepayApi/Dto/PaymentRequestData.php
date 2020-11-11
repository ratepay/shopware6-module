<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentRequestData extends OrderOperationData
{
    /**
     * @var OrderTransactionEntity
     */
    protected $transaction;

    /**
     * @var RequestDataBag
     */
    private $requestDataBag;

    /**
     * @var SalesChannelContext
     */
    private $salesChannelContext;

    /**
     * @var ProfileConfigEntity
     */
    private $profileConfig;

    public function __construct(
        SalesChannelContext $salesChannelContext,
        OrderEntity $order,
        OrderTransactionEntity $transaction,
        ProfileConfigEntity $profileConfig,
        RequestDataBag $requestDataBag
    ) {
        parent::__construct($salesChannelContext->getContext(), $order, self::OPERATION_REQUEST, null, false);
        $this->transaction = $transaction;
        $this->requestDataBag = $requestDataBag;
        $this->salesChannelContext = $salesChannelContext;
        $this->profileConfig = $profileConfig;
    }

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
            $items['shipping'] = 1;
        }

        return $items;
    }

    public function getTransaction(): OrderTransactionEntity
    {
        return $this->transaction;
    }

    public function getRequestDataBag(): RequestDataBag
    {
        return $this->requestDataBag;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function getProfileConfig(): ProfileConfigEntity
    {
        return $this->profileConfig;
    }
}
