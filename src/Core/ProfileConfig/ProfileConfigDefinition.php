<?php


namespace Ratepay\RatepayPayments\Core\ProfileConfig;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use \Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity as Entity;

class ProfileConfigDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ratepay_profile_config';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ProfileConfigEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProfileConfigCollection::class;
    }

    protected function defineFields(): FieldCollection
    {

        //TODO primary keys over more than one column. Shopware does not support this currently
        return new FieldCollection([

            (new IdField(
                'id',
                Entity::FIELD_ID
            ))->addFlags(new Required(), new PrimaryKey()),

            (new StringField(
                'profile_id',
                Entity::FIELD_PROFILE_ID
            ))->addFlags(new Required()),

            (new StringField(
                'security_code',
                Entity::FIELD_SECURITY_CODE
            ))->addFlags(new Required()),

            (new BoolField(
                'sandbox',
                Entity::FIELD_SANDBOX
            ))->addFlags(new Required()/*, new PrimaryKey()*/),

            (new BoolField(
                'backend',
                Entity::FIELD_BACKEND
            ))->addFlags(new Required()/*, new PrimaryKey()*/),

            (new OneToOneAssociationField(
                Entity::FIELD_SALES_CHANNEL,
                'sales_channel_id',
                'id',
                SalesChannelDefinition::class,
                false
            ))->addFlags(new CascadeDelete()),

            (new IdField(
                'sales_channel_id',
                Entity::FIELD_SALES_CHANNEL_ID
            )),

            (new StringField(
                'country_code_billing',
                Entity::FIELD_COUNTRY_CODE_BILLING
            )),
            (new StringField(
                'country_code_delivery',
                Entity::FIELD_COUNTRY_CODE_SHIPPING
            )),

            (new StringField(
                'currency',
                Entity::FIELD_CURRENCY
            )),

            (new BoolField(
                'status',
                Entity::FIELD_STATUS
            )),

            (new StringField(
                'status_message',
                Entity::FIELD_STATUS_MESSAGE
            )),

            (new OneToManyAssociationField(
                Entity::FIELD_PAYMENT_METHOD_CONFIGS,
                ProfileConfigMethodDefinition::class,
                'profile_id',
                ProfileConfigEntity::FIELD_ID
            ))
        ]);
    }
}
