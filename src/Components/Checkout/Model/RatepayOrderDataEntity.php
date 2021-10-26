<?php

/*
 * Copyright (c) Ratepay GmbH
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
    use EntityIdTrait;

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

    public const FIELD_SEND_DISCOUNT_AS_CART_ITEM = 'sendDiscountAsCartItem';

    public const FIELD_SEND_SHIPPING_COSTS_AS_CART_ITEM = 'sendShippingCostsAsCartItem';

    protected string $orderId;

    protected string $orderVersionId;

    protected ?OrderEntity $order = null;

    protected string $profileId;

    protected string $transactionId;

    protected ?string $descriptor = null;

    protected ?string $shippingPositionId = null;

    protected ?RatepayPositionEntity $shippingPosition = null;

    protected bool $successful = false;

    protected bool $sendDiscountAsCartItem = false;

    protected bool $sendShippingCostsAsCartItem = false;

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getOrderVersionId(): string
    {
        return $this->orderVersionId;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function getTransactionId(): string
    {
        return $this->transactionId;
    }

    public function getDescriptor(): ?string
    {
        return $this->descriptor;
    }

    public function getShippingPositionId(): ?string
    {
        return $this->shippingPositionId;
    }

    public function getShippingPosition(): ?RatepayPositionEntity
    {
        return $this->shippingPosition;
    }

    public function getProfileId(): string
    {
        return $this->profileId;
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function isSendDiscountAsCartItem(): bool
    {
        return $this->sendDiscountAsCartItem;
    }

    public function isSendShippingCostsAsCartItem(): bool
    {
        return $this->sendShippingCostsAsCartItem;
    }
}
