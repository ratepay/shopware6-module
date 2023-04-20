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
 * @extends EntityCollection<ProfileConfigMethodEntity>
 */
class ProfileConfigMethodCollection extends EntityCollection
{
    /**
     * @return EntityCollection<ProfileConfigMethodEntity>
     */
    public function filterByMethod(string $paymentMethodId): EntityCollection
    {
        return $this->filterByProperty(ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD_ID, $paymentMethodId);
    }

    protected function getExpectedClass(): string
    {
        return ProfileConfigMethodEntity::class;
    }
}
