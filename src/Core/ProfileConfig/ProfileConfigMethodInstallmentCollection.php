<?php


namespace Ratepay\RatepayPayments\Core\ProfileConfig;


use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigMethodInstallmentEntity as Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(Entity $entity)
 * @method void set(string $key, Entity $entity)
 * @method Entity[] getIterator()
 * @method Entity[] getElements()
 * @method Entity|null get(string $key)
 * @method Entity|null first()
 * @method Entity|null last()
 */
class ProfileConfigMethodInstallmentCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return Entity::class;
    }
}
