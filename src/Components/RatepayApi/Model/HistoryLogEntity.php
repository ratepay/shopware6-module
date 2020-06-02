<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Model;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class HistoryLogEntity extends Entity
{

    use EntityIdTrait;

    /**
     * @var int
     */
    protected $id;

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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

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


