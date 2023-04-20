<?php

declare(strict_types=1);

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

    /**
     * @var string
     */
    public const FIELD_ID = 'id';

    /**
     * @var string
     */
    public const FIELD_ORDER_LINE_ITEM_ID = 'orderLineItemId';

    /**
     * @var string
     */
    public const FIELD_ORDER_LINE_ITEM_VERSION_ID = 'orderLineItemVersionId';

    /**
     * @var string
     */
    public const FIELD_ORDER_LINE_ITEM = 'orderLineItem';

    /**
     * @var string
     */
    public const FIELD_POSITION_ID = 'positionId';

    /**
     * @var string
     */
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
