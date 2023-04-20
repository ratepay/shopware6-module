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
 * @extends EntityCollection<TransactionIdEntity>
 */
class TransactionIdCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TransactionIdEntity::class;
    }
}
