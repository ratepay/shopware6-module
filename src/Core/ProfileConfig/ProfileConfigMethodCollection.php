<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Core\ProfileConfig;


use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(ProfileConfigMethodEntity $entity)
 * @method void set(string $key, ProfileConfigMethodEntity $entity)
 * @method ProfileConfigMethodEntity[] getIterator()
 * @method ProfileConfigMethodEntity[] getElements()
 * @method ProfileConfigMethodEntity|null get(string $key)
 * @method ProfileConfigMethodEntity|null first()
 * @method ProfileConfigMethodEntity|null last()
 */
class ProfileConfigMethodCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProfileConfigMethodEntity::class;
    }
}
