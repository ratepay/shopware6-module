<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Model\Collection;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodInstallmentEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<ProfileConfigMethodInstallmentEntity>
 */
class ProfileConfigMethodInstallmentCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProfileConfigMethodInstallmentEntity::class;
    }
}
