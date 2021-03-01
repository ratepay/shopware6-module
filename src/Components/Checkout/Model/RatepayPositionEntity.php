<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Model;

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

    public function getCanceled(): int
    {
        return $this->canceled;
    }

    public function setCanceled(int $canceled): void
    {
        $this->canceled = $canceled;
    }

    public function getReturned(): int
    {
        return $this->returned;
    }

    public function setReturned(int $returned): void
    {
        $this->returned = $returned;
    }

    public function getDelivered(): int
    {
        return $this->delivered;
    }

    public function setDelivered(int $delivered): void
    {
        $this->delivered = $delivered;
    }
}
