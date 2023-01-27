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
    use EntityIdTrait;

    /**
     * @var string
     */
    public const FIELD_ID = 'id';

    /**
     * @var string
     */
    public const FIELD_CANCELED = 'canceled';

    /**
     * @var string
     */
    public const FIELD_RETURNED = 'returned';

    /**
     * @var string
     */
    public const FIELD_DELIVERED = 'delivered';

    protected int $canceled = 0;

    protected int $returned = 0;

    protected int $delivered = 0;

    public function getCanceled(): int
    {
        return $this->canceled;
    }

    public function getReturned(): int
    {
        return $this->returned;
    }

    public function getDelivered(): int
    {
        return $this->delivered;
    }
}
