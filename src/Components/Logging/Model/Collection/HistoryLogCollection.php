<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Model\Collection;

use Ratepay\RpayPayments\Components\Logging\Model\HistoryLogEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<HistoryLogEntity>
 */
class HistoryLogCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return HistoryLogEntity::class;
    }
}
