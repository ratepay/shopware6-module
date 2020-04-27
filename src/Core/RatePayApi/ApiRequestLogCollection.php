<?php


namespace Ratepay\RatepayPayments\Core\RatepayApi;


use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(ApiRequestLogEntity $entity)
 * @method void set(string $key, ApiRequestLogEntity $entity)
 * @method ApiRequestLogEntity[] getIterator()
 * @method ApiRequestLogEntity[] getElements()
 * @method ApiRequestLogEntity|null get(string $key)
 * @method ApiRequestLogEntity|null first()
 * @method ApiRequestLogEntity|null last()
 */
class ApiRequestLogCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ApiRequestLogEntity::class;
    }
}
