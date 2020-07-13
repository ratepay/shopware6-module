<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Dto;


use Ratepay\RatepayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
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
    )
    {
        parent::__construct($order, self::OPERATION_REQUEST, null);
        $this->transaction = $transaction;
        $this->requestDataBag = $requestDataBag;
        $this->salesChannelContext = $salesChannelContext;
        $this->profileConfig = $profileConfig;
    }

    /**
     * @return array
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
            $items['shipping'] = 1;
        }
        return $items;
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

    /**
     * @return ProfileConfigEntity
     */
    public function getProfileConfig(): ProfileConfigEntity
    {
        return $this->profileConfig;
    }
}
