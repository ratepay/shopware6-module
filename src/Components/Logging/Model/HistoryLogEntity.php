<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Logging\Model;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class HistoryLogEntity extends Entity
{

    public const FIELD_ID = 'id';
    public const FIELD_ORDER_ID = 'orderId';
    public const FIELD_EVENT = 'event';
    public const FIELD_USER = 'user';
    public const FIELD_PRODUCT_NAME = 'productName';
    public const FIELD_PRODUCT_NUMBER = 'productNumber';
    public const FIELD_QTY = 'quantity';

    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $event;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $productName;

    /**
     * @var string
     */
    protected $productNumber;
    /**
     * @var string
     */
    protected $quantity;

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * @param string $orderId
     */
    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @param string $event
     */
    public function setEvent(string $event): void
    {
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getProductName(): string
    {
        return $this->productName;
    }

    /**
     * @param string $productName
     */
    public function setProductName(string $productName): void
    {
        $this->productName = $productName;
    }

    /**
     * @return string
     */
    public function getProductNumber(): string
    {
        return $this->productNumber;
    }

    /**
     * @param string $productNumber
     */
    public function setProductNumber(string $productNumber): void
    {
        $this->productNumber = $productNumber;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

}


