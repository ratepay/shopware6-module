<?php


namespace RatePay\RatePayPayments\Core\ProfileConfig;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProfileConfigMethodDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ratepay_profile_config_method';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ProfileConfigMethodEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProfileConfigMethodCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new BoolField('b2b', 'b2b'))->addFlags(new Required()),
            (new IntField('limit_min', 'limitMin'))->addFlags(new Required()),
            (new IntField('limit_max', 'limitMax'))->addFlags(new Required()),
            (new IntField('limit_max_b2b', 'limitMaxB2b')),
            (new BoolField('allow_different_addresses', 'allowDifferentAddresses')),
        ]);
    }
}
