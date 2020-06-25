<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\ProfileConfig\Model\Collection;

use Ratepay\RatepayPayments\Components\ProfileConfig\Model\ProfileConfigMethodInstallmentEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(ProfileConfigMethodInstallmentEntity $entity)
 * @method void set(string $key, ProfileConfigMethodInstallmentEntity $entity)
 * @method ProfileConfigMethodInstallmentEntity[] getIterator()
 * @method ProfileConfigMethodInstallmentEntity[] getElements()
 * @method ProfileConfigMethodInstallmentEntity|null get(string $key)
 * @method ProfileConfigMethodInstallmentEntity|null first()
 * @method ProfileConfigMethodInstallmentEntity|null last()
 */
class ProfileConfigMethodInstallmentCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProfileConfigMethodInstallmentEntity::class;
    }
}
