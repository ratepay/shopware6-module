<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Model;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class HistoryLogEntity extends Entity
{
    public const FIELD_ID = 'id';

    public const FIELD_ORDER = 'order';

    public const FIELD_ORDER_ID = 'orderId';

    public const FIELD_EVENT = 'event';

    public const FIELD_USER = 'user';

    public const FIELD_PRODUCT_NAME = 'productName';

    public const FIELD_PRODUCT_NUMBER = 'productNumber';

    public const FIELD_QTY = 'quantity';

    protected string $orderId;

    protected ?OrderEntity $order = null;

    protected string $event;

    protected string $user;

    protected string $productName;

    protected string $productNumber;

    protected int $quantity;

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(?OrderEntity $order): void
    {
        $this->order = $order;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function setEvent(string $event): void
    {
        $this->event = $event;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    public function getProductName(): string
    {
        return $this->productName;
    }

    public function setProductName(string $productName): void
    {
        $this->productName = $productName;
    }

    public function getProductNumber(): string
    {
        return $this->productNumber;
    }

    public function setProductNumber(string $productNumber): void
    {
        $this->productNumber = $productNumber;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }
}
