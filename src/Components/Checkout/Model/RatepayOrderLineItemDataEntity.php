<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Model;

use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class RatepayOrderLineItemDataEntity extends Entity
{
    use EntityIdTrait;

    public const FIELD_ID = 'id';

    public const FIELD_ORDER_LINE_ITEM_ID = 'orderLineItemId';

    public const FIELD_ORDER_LINE_ITEM_VERSION_ID = 'orderLineItemVersionId';

    public const FIELD_ORDER_LINE_ITEM = 'orderLineItem';

    public const FIELD_POSITION_ID = 'positionId';

    public const FIELD_POSITION = 'position';

    protected string $orderLineItemId;

    protected string $orderLineItemVersionId;

    protected ?OrderLineItemEntity $orderLineItem = null;

    protected string $positionId;

    protected ?RatepayPositionEntity $position = null;

    public function getOrderLineItemId(): string
    {
        return $this->orderLineItemId;
    }

    public function getOrderLineItemVersionId(): string
    {
        return $this->orderLineItemVersionId;
    }

    public function getOrderLineItem(): ?OrderLineItemEntity
    {
        return $this->orderLineItem;
    }

    public function getPositionId(): string
    {
        return $this->positionId;
    }

    public function getPosition(): ?RatepayPositionEntity
    {
        return $this->position;
    }
}
