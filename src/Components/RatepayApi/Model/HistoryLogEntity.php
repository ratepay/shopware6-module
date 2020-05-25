<?php

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
    protected $date;

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
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate(string $date): void
    {
        $this->date = $date;
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
     * @return string
     */
    public function getQuantity(): string
    {
        return $this->quantity;
    }

    /**
     * @param string $quantity
     */
    public function setQuantity(string $quantity): void
    {
        $this->quantity = $quantity;
    }

}

