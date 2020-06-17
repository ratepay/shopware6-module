<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Dto;


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

    public function __construct(SalesChannelContext $salesChannelContext, OrderEntity $order, OrderTransactionEntity $transaction, RequestDataBag $requestDataBag)
    {
        parent::__construct($order, self::OPERATION_REQUEST, null);
        $this->transaction = $transaction;
        $this->requestDataBag = $requestDataBag;
        $this->salesChannelContext = $salesChannelContext;
    }

    /**
     * @return OrderTransactionEntity
     */
    public function getTransaction(): OrderTransactionEntity
    {
        return $this->transaction;
    }

    /**
     * @return RequestDataBag
     */
    public function getRequestDataBag(): RequestDataBag
    {
        return $this->requestDataBag;
    }

    /**
     * @return SalesChannelContext
     */
    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }
}
