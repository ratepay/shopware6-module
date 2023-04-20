<?php

/*
 * Copyright (c) Ratepay GmbH
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
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class OrderOperationData extends AbstractRequestData implements OperationDataWithBasket
{
    /**
     * @var string
     */
    public const OPERATION_REQUEST = 'request';

    /**
     * @var string
     */
    public const OPERATION_DELIVER = 'deliver';

    /**
     * @var string
     */
    public const OPERATION_CANCEL = 'cancel';

    /**
     * @var string
     */
    public const OPERATION_RETURN = 'return';

    /**
     * @var string
     */
    public const OPERATION_ADD = 'add';

    /**
     * @var string
     */
    public const ITEM_ID_SHIPPING = 'shipping';

    protected OrderEntity $order;

    protected ?array $items;

    protected ?OrderTransactionEntity $transaction;

    protected string $operation;

    protected bool $updateStock;

    public function __construct(Context $context, OrderEntity $order, string $operation, ?array $items = null, bool $updateStock = true)
    {
        parent::__construct($context);
        $this->order = $order;
        $this->transaction = $order->getTransactions() !== null ? $order->getTransactions()->last() : null;
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

        $orderExtension = $this->order->getExtension(OrderExtension::EXTENSION_NAME);
        if ($this->order->getShippingTotal() > 0 &&
            $orderExtension instanceof RatepayOrderDataEntity &&
            ($shippingPosition = $orderExtension->getShippingPosition())
        ) {
            $quantity = $this->getMaxQuantityForOperation($shippingPosition, 1);
            if ($quantity > 0) {
                $items[self::ITEM_ID_SHIPPING] = $quantity;
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

    public function getTransaction(): ?OrderTransactionEntity
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

    public function getCurrencyCode(): string
    {
        return $this->order->getCurrency()->getIsoCode();
    }

    public function getShippingCosts(): ?CalculatedPrice
    {
        return $this->order->getShippingCosts();
    }

    public function isSendDiscountAsCartItem(): bool
    {
        /** @var RatepayOrderDataEntity|null $orderExtension */
        $orderExtension = $this->order->getExtension(OrderExtension::EXTENSION_NAME);
        return $orderExtension && $orderExtension->isSendDiscountAsCartItem();
    }

    public function isSendShippingCostsAsCartItem(): bool
    {
        /** @var RatepayOrderDataEntity|null $orderExtension */
        $orderExtension = $this->order->getExtension(OrderExtension::EXTENSION_NAME);
        return $orderExtension && $orderExtension->isSendShippingCostsAsCartItem();
    }
}
