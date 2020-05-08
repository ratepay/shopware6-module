<?php


namespace Ratepay\RatepayPayments\Core\ProfileConfig;

use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigMethodInstallmentEntity as Entity;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigMethodInstallmentCollection as Collection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ListField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProfileConfigMethodInstallmentDefinition extends EntityDefinition
{

    public const ENTITY_NAME = 'ratepay_profile_config_method_installment';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return Entity::class;
    }

    public function getCollectionClass(): string
    {
        return Collection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', Entity::FIELD_ID))->addFlags(new Required(), new PrimaryKey()),
            (new OneToOneAssociationField(Entity::FIELD_CONFIG, 'id', ProfileConfigMethodEntity::FIELD_ID, ProfileConfigMethodDefinition::class))->addFlags(new CascadeDelete()),
            (new ListField('month_allowed', Entity::FIELD_ALLOWED_MONTHS, IntField::class))->addFlags(new Required()),
            (new BoolField('is_banktransfer_allowed', Entity::FIELD_IS_BANKTRANSFER_ALLOWED))->addFlags(new Required()),
            (new BoolField('is_debit_allowed', Entity::FIELD_IS_DEBIT_ALLOWED))->addFlags(new Required()),
            (new FloatField('rate_min_normal', Entity::FIELD_RATE_MIN))->addFlags(new Required()),
        ]);
    }
}
