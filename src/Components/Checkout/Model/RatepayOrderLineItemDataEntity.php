<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Model;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class RatepayOrderLineItemDataEntity extends Entity
{

    public const FIELD_ID = 'id';
    public const FIELD_POSITION_ID = 'positionId';
    public const FIELD_POSITION = 'position';

    use EntityIdTrait;

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
