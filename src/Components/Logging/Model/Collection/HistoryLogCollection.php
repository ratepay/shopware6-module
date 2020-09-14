<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Model\Collection;

use Ratepay\RpayPayments\Components\Logging\Model\HistoryLogEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(HistoryLogEntity $entity)
 * @method void set(string $key, HistoryLogEntity $entity)
 * @method HistoryLogEntity[] getIterator()
 * @method HistoryLogEntity[] getElements()
 * @method HistoryLogEntity|null get(string $key)
 * @method HistoryLogEntity|null first()
 * @method HistoryLogEntity|null last()
 */
class HistoryLogCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return HistoryLogEntity::class;
    }
}
