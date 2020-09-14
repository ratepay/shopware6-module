<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
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

    public const FIELD_ID = 'id';
    public const FIELD_ORDER_LINE_ITEM_ID = 'orderLineItemId';
    public const FIELD_ORDER_LINE_ITEM_VERSION_ID = 'orderLineItemVersionId';
    public const FIELD_ORDER_LINE_ITEM = 'orderLineItem';
    public const FIELD_POSITION_ID = 'positionId';
    public const FIELD_POSITION = 'position';


    use EntityIdTrait;

    /**
     * @var string
     */
    protected $orderLineItemId;

    /**
     * @var string
     */
    protected $orderLineItemVersionId;

    /**
     * @var OrderLineItemEntity
     */
    protected $orderLineItem;

    /**
     * @var string
     */
    protected $positionId;

    /**
     * @var RatepayPositionEntity
     */
    protected $position;

    /**
     * @return string
     */
    public function getOrderLineItemId(): string
    {
        return $this->orderLineItemId;
    }

    /**
     * @param string $orderLineItemId
     */
    public function setOrderLineItemId(string $orderLineItemId): void
    {
        $this->orderLineItemId = $orderLineItemId;
    }

    /**
     * @return string
     */
    public function getOrderLineItemVersionId(): string
    {
        return $this->orderLineItemVersionId;
    }

    /**
     * @param string $orderLineItemVersionId
     */
    public function setOrderLineItemVersionId(string $orderLineItemVersionId): void
    {
        $this->orderLineItemVersionId = $orderLineItemVersionId;
    }

    /**
     * @return OrderLineItemEntity
     */
    public function getOrderLineItem(): OrderLineItemEntity
    {
        return $this->orderLineItem;
    }

    /**
     * @param OrderLineItemEntity $orderLineItem
     */
    public function setOrderLineItem(OrderLineItemEntity $orderLineItem): void
    {
        $this->orderLineItem = $orderLineItem;
    }

    /**
     * @return string
     */
    public function getPositionId(): string
    {
        return $this->positionId;
    }

    /**
     * @param string $positionId
     */
    public function setPositionId(string $positionId): void
    {
        $this->positionId = $positionId;
    }

    /**
     * @return RatepayPositionEntity
     */
    public function getPosition(): RatepayPositionEntity
    {
        return $this->position;
    }

    /**
     * @param RatepayPositionEntity $position
     */
    public function setPosition(RatepayPositionEntity $position): void
    {
        $this->position = $position;
    }
}
