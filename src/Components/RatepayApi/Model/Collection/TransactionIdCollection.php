<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Model\Collection;

use Ratepay\RpayPayments\Components\RatepayApi\Model\TransactionIdEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                     add(TransactionIdEntity $entity)
 * @method void                     set(string $key, TransactionIdEntity $entity)
 * @method TransactionIdEntity[]    getIterator()
 * @method TransactionIdEntity[]    getElements()
 * @method TransactionIdEntity|null get(string $key)
 * @method TransactionIdEntity|null first()
 * @method TransactionIdEntity|null last()
 */
class TransactionIdCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TransactionIdEntity::class;
    }
}
