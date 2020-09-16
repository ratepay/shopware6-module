<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler\Event;

use RatePAY\Model\Response\PaymentRequest;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareEvent;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractPaymentEvent extends Event implements ShopwareEvent
{
    /**
     * @var SyncPaymentTransactionStruct
     */
    private $transaction;

    /**
     * @var RequestDataBag
     */
    private $requestDataBag;

    /**
     * @var SalesChannelContext
     */
    private $salesChannelContext;

    /**
     * @var PaymentRequest
     */
    private $response;

    /**
     * @var OrderEntity
     */
    private $order;

    public function __construct(
        OrderEntity $order,
        SyncPaymentTransactionStruct $transaction,
        RequestDataBag $requestDataBag,
        SalesChannelContext $salesChannelContext,
        PaymentRequest $response = null
    ) {
        $this->transaction = $transaction;
        $this->requestDataBag = $requestDataBag;
        $this->salesChannelContext = $salesChannelContext;
        $this->response = $response;
        $this->order = $order;
    }

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    public function getContext(): Context
    {
        return $this->salesChannelContext->getContext();
    }

    public function getTransaction(): SyncPaymentTransactionStruct
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

    /**
     * @return PaymentRequest
     */
    public function getResponse(): ?PaymentRequest
    {
        return $this->response;
    }
}
