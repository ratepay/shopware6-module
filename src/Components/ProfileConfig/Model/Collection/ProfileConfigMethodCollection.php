<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Model\Collection;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                           add(ProfileConfigMethodEntity $entity)
 * @method void                           set(string $key, ProfileConfigMethodEntity $entity)
 * @method ProfileConfigMethodEntity[]    getIterator()
 * @method ProfileConfigMethodEntity[]    getElements()
 * @method ProfileConfigMethodEntity|null get(string $key)
 * @method ProfileConfigMethodEntity|null first()
 * @method ProfileConfigMethodEntity|null last()
 */
class ProfileConfigMethodCollection extends EntityCollection
{
    public function filterByMethod(string $paymentMethodId)
    {
        return $this->filterByProperty(ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD_ID, $paymentMethodId);
    }

    protected function getExpectedClass(): string
    {
        return ProfileConfigMethodEntity::class;
    }
}
