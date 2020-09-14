<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Model;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class RatepayOrderDataEntity extends Entity
{

    public const FIELD_ID = 'id';
    public const FIELD_ORDER_ID = 'orderId';
    public const FIELD_ORDER_VERSION_ID = 'orderVersionId';
    public const FIELD_ORDER = 'order';
    public const FIELD_PROFILE_ID = 'profileId';
    public const FIELD_TRANSACTION_ID = 'transactionId';
    public const FIELD_DESCRIPTOR = 'descriptor';
    public const FIELD_SHIPPING_POSITION_ID = 'shippingPositionId';
    public const FIELD_SHIPPING_POSITION = 'shippingPosition';
    public const FIELD_SUCCESSFUL = 'successful';

    use EntityIdTrait;


    /**
     * @var string
     */
    protected $orderId;

    /**
     * @var string
     */
    protected $orderVersionId;

    /**
     * @var OrderEntity
     */
    protected $order;

    /**
     * @var string
     */
    protected $profileId;

    /**
     * @var string
     */
    protected $transactionId;

    /**
     * @var string|null
     */
    protected $descriptor;

    /**
     * @var string|null
     */
    protected $shippingPositionId;

    /**
     * @var RatepayPositionEntity|null
     */
    protected $shippingPosition;

    /**
     * @var bool
     */
    protected $successful;

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
    public function getOrderVersionId(): string
    {
        return $this->orderVersionId;
    }

    /**
     * @param string $orderVersionId
     */
    public function setOrderVersionId(string $orderVersionId): void
    {
        $this->orderVersionId = $orderVersionId;
    }

    /**
     * @return OrderEntity
     */
    public function getOrder(): OrderEntity
    {
        return $this->order;
    }

    /**
     * @param OrderEntity $order
     */
    public function setOrder(OrderEntity $order): void
    {
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId(string $transactionId): void
    {
        $this->transactionId = $transactionId;
    }

    /**
     * @return null|string
     */
    public function getDescriptor(): ?string
    {
        return $this->descriptor;
    }

    /**
     * @return string|null
     */
    public function getShippingPositionId(): ?string
    {
        return $this->shippingPositionId;
    }

    /**
     * @param string|null $shippingPositionId
     */
    public function setShippingPositionId(?string $shippingPositionId): void
    {
        $this->shippingPositionId = $shippingPositionId;
    }

    /**
     * @return RatepayPositionEntity|null
     */
    public function getShippingPosition(): ?RatepayPositionEntity
    {
        return $this->shippingPosition;
    }

    /**
     * @param RatepayPositionEntity|null $shippingPosition
     */
    public function setShippingPosition(?RatepayPositionEntity $shippingPosition): void
    {
        $this->shippingPosition = $shippingPosition;
    }

    /**
     * @return string
     */
    public function getProfileId(): string
    {
        return $this->profileId;
    }

    /**
     * @param string $profileId
     */
    public function setProfileId(string $profileId): void
    {
        $this->profileId = $profileId;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->successful;
    }
}
