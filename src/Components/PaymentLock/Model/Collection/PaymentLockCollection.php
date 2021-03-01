<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentLock\Model\Collection;

use Ratepay\RpayPayments\Components\PaymentLock\Model\PaymentLockEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                   add(PaymentLockEntity $entity)
 * @method void                   set(string $key, PaymentLockEntity $entity)
 * @method PaymentLockEntity[]    getIterator()
 * @method PaymentLockEntity[]    getElements()
 * @method PaymentLockEntity|null get(string $key)
 * @method PaymentLockEntity|null first()
 * @method PaymentLockEntity|null last()
 */
class PaymentLockCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return PaymentLockEntity::class;
    }
}
