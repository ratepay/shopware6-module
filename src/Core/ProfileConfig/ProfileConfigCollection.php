<?php


namespace Ratepay\RatepayPayments\Core\ProfileConfig;


use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(ProfileConfigEntity $entity)
 * @method void set(string $key, ProfileConfigEntity $entity)
 * @method ProfileConfigEntity[] getIterator()
 * @method ProfileConfigEntity[] getElements()
 * @method ProfileConfigEntity|null get(string $key)
 * @method ProfileConfigEntity|null first()
 * @method ProfileConfigEntity|null last()
 */
class ProfileConfigCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProfileConfigEntity::class;
    }
}
