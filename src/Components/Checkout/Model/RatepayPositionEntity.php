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

class RatepayPositionEntity extends Entity
{

    public const FIELD_ID = 'id';
    public const FIELD_CANCELED = 'canceled';
    public const FIELD_RETURNED = 'returned';
    public const FIELD_DELIVERED = 'delivered';

    use EntityIdTrait;

    /**
     * @var int
     */
    protected $canceled = 0;

    /**
     * @var int
     */
    protected $returned = 0;

    /**
     * @var int
     */
    protected $delivered = 0;

    /**
     * @return int
     */
    public function getCanceled(): int
    {
        return $this->canceled;
    }

    /**
     * @param int $canceled
     */
    public function setCanceled(int $canceled): void
    {
        $this->canceled = $canceled;
    }

    /**
     * @return int
     */
    public function getReturned(): int
    {
        return $this->returned;
    }

    /**
     * @param int $returned
     */
    public function setReturned(int $returned): void
    {
        $this->returned = $returned;
    }

    /**
     * @return int
     */
    public function getDelivered(): int
    {
        return $this->delivered;
    }

    /**
     * @param int $delivered
     */
    public function setDelivered(int $delivered): void
    {
        $this->delivered = $delivered;
    }
}