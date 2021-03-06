<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Model\Collection;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                     add(ProfileConfigEntity $entity)
 * @method void                     set(string $key, ProfileConfigEntity $entity)
 * @method ProfileConfigEntity[]    getIterator()
 * @method ProfileConfigEntity[]    getElements()
 * @method ProfileConfigEntity|null get(string $key)
 * @method ProfileConfigEntity|null first()
 * @method ProfileConfigEntity|null last()
 */
class ProfileConfigCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProfileConfigEntity::class;
    }
}
