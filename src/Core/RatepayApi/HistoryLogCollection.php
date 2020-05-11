<?php


namespace Ratepay\RatepayPayments\Core\RatepayApi;

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
