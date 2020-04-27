<?php


namespace RatePay\RatePayPayments\Core\ProfileConfig;


use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(ProfileConfigMethodEntity $entity)
 * @method void set(string $key, ProfileConfigMethodEntity $entity)
 * @method ProfileConfigMethodEntity[] getIterator()
 * @method ProfileConfigMethodEntity[] getElements()
 * @method ProfileConfigMethodEntity|null get(string $key)
 * @method ProfileConfigMethodEntity|null first()
 * @method ProfileConfigMethodEntity|null last()
 */
class ProfileConfigMethodCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProfileConfigMethodEntity::class;
    }
}
