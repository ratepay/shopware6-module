<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;

use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderLineItemExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderLineItemDataEntity;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayPositionEntity;
use Ratepay\RpayPayments\Components\OrderManagement\Util\LineItemUtil;
use RuntimeException;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class OrderOperationData extends AbstractRequestData
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

    /**
     * OrderOperationData constructor.
     *
     * @param array|null $items       array of IDs and quantity. if no array is provided, all items will be sent to the gateway, if the operation is allowed
     * @param bool       $updateStock
     */
    public function __construct(Context $context, OrderEntity $order, string $operation, $items = null, $updateStock = true)
    {
        parent::__construct($context);
        $this->order = $order;
        $this->transaction = $order->getTransactions() ? $order->getTransactions()->last() : null;
        $this->items = $items;
        $this->operation = $operation;
        $this->updateStock = $items === null ? false : $updateStock;
    }

    public function getItems(): array
    {
        if ($this->items) {
            return $this->items;
        }

        $items = [];
        foreach ($this->order->getLineItems() as $lineItem) {
            /** @var RatepayOrderLineItemDataEntity $extension */
            if ($extension = $lineItem->getExtension(OrderLineItemExtension::EXTENSION_NAME)) {
                $quantity = $this->getMaxQuantityForOperation($extension->getPosition(), $lineItem->getQuantity());
                if ($quantity > 0) {
                    $items[$lineItem->getId()] = $quantity;
                }
            }
        }

        /** @var RatepayOrderDataEntity $orderExtension */
        if ($this->order->getShippingTotal() > 0 &&
            ($orderExtension = $this->order->getExtension(OrderExtension::EXTENSION_NAME)) &&
            ($shippingPosition = $orderExtension->getShippingPosition())
        ) {
            $quantity = $this->getMaxQuantityForOperation($shippingPosition, 1);
            if ($quantity > 0) {
                $items['shipping'] = $quantity;
            }
        }

        return $items;
    }

    private function getMaxQuantityForOperation(RatepayPositionEntity $position, int $ordered): int
    {
        $maxValues = LineItemUtil::addMaxActionValues($position, $ordered);
        switch ($this->operation) {
            case self::OPERATION_DELIVER:
                return $maxValues['maxDelivery'];
            case self::OPERATION_CANCEL:
                return $maxValues['maxCancel'];
            case self::OPERATION_RETURN:
                return $maxValues['maxReturn'];
            default:
                throw new RuntimeException('the operation ' . $this->operation . '` is not supported for automatic delivery');
        }
    }

    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    public function getTransaction(): OrderTransactionEntity
    {
        return $this->transaction;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function isUpdateStock(): bool
    {
        return $this->updateStock;
    }
}
