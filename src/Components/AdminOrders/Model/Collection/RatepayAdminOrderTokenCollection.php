<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\AdminOrders\Model\Collection;

use Ratepay\RpayPayments\Components\AdminOrders\Model\RatepayAdminOrderTokenEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                       add(RatepayAdminOrderTokenEntity $entity)
 * @method void                       set(string $key, RatepayAdminOrderTokenEntity $entity)
 * @method RatepayAdminOrderTokenEntity[]    getIterator()
 * @method RatepayAdminOrderTokenEntity[]    getElements()
 * @method RatepayAdminOrderTokenEntity|null get(string $key)
 * @method RatepayAdminOrderTokenEntity|null first()
 * @method RatepayAdminOrderTokenEntity|null last()
 */
class RatepayAdminOrderTokenCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return RatepayAdminOrderTokenEntity::class;
    }
}
