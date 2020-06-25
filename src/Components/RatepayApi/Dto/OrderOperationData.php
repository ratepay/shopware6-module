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

class OrderOperationData implements IRequestData
{

    public const OPERATION_REQUEST = 'request';
    public const OPERATION_DELIVER = 'deliver';
    public const OPERATION_CANCEL = 'cancel';
    public const OPERATION_RETURN = 'return';
    public const OPERATION_ADD = 'add';

    /**
     * @var OrderEntity
     */
    protected $order;
    /**
     * @var null
     */
    protected $items;
    /**
     * @var OrderTransactionEntity
     */
    protected $transaction;
    /**
     * @var string
     */
    protected $operation;
    /**
     * @var bool
     */
    protected $updateStock;

    public function __construct(OrderEntity $order, string $operation, $items = null, $updateStock = true)
    {
        $this->order = $order;
        $this->transaction = $order->getTransactions() ? $order->getTransactions()->first() : null;
        $this->items = $items;
        $this->operation = $operation;
        $this->updateStock = $items === null ? false : $updateStock;
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
     * @return OrderEntity
     */
    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    /**
     * @return OrderTransactionEntity
     */
    public function getTransaction(): OrderTransactionEntity
    {
        return $this->transaction;
    }

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * @return bool
     */
    public function isUpdateStock(): bool
    {
        return $this->updateStock;
    }
}
