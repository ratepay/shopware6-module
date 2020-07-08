<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Model;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class RatepayOrderDataEntity extends Entity
{

    public const FIELD_ID = 'id';
    public const FIELD_TRANSACTION_ID = 'transactionId';
    public const FIELD_SHIPPING_POSITION_ID = 'shippingPositionId';
    public const FIELD_SHIPPING_POSITION = 'shippingPosition';

    use EntityIdTrait;

    /**
     * @var string
     */
    protected $transactionId;

    /**
     * @var string
     */
    protected $shippingPositionId;

    /**
     * @var RatepayPositionEntity
     */
    protected $shippingPosition;

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
     * @return string
     */
    public function getShippingPositionId(): string
    {
        return $this->shippingPositionId;
    }

    /**
     * @param string $shippingPositionId
     */
    public function setShippingPositionId(string $shippingPositionId): void
    {
        $this->shippingPositionId = $shippingPositionId;
    }

    /**
     * @return RatepayPositionEntity
     */
    public function getShippingPosition(): RatepayPositionEntity
    {
        return $this->shippingPosition;
    }

    /**
     * @param RatepayPositionEntity $shippingPosition
     */
    public function setShippingPosition(RatepayPositionEntity $shippingPosition): void
    {
        $this->shippingPosition = $shippingPosition;
    }
}
