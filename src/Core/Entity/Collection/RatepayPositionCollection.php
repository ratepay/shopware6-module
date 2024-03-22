<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Core\Entity\Collection;

use Ratepay\RpayPayments\Core\Entity\RatepayPositionEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<RatepayPositionEntity>
 */
class RatepayPositionCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return RatepayPositionEntity::class;
    }
}
