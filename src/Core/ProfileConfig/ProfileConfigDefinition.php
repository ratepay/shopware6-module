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
            (new IdField('id', ProfileConfigEntity::FIELD_ID))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('profile_id', ProfileConfigEntity::FIELD_PROFILE_ID))->addFlags(new Required()),
            (new StringField('security_code', ProfileConfigEntity::FIELD_SECURITY_CODE))->addFlags(new Required()),
            (new BoolField('sandbox', ProfileConfigEntity::FIELD_SANDBOX))->addFlags(new Required()/*, new PrimaryKey()*/),
            (new BoolField('backend', ProfileConfigEntity::FIELD_BACKEND))->addFlags(new Required()/*, new PrimaryKey()*/),

            (new OneToOneAssociationField(ProfileConfigEntity::FIELD_SALES_CHANNEL, 'sales_channel_id', 'id', SalesChannelDefinition::class))->addFlags(new CascadeDelete()),
            (new IdField('sales_channel_id', ProfileConfigEntity::FIELD_SALES_CHANNEL_ID)),

            (new StringField('country_code_billing', ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING)),
            (new StringField('country_code_delivery', ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING)),
            (new StringField('currency', ProfileConfigEntity::FIELD_CURRENCY)),


            (new BoolField('status', ProfileConfigEntity::FIELD_STATUS)),
            (new StringField('status_message', ProfileConfigEntity::FIELD_STATUS_MESSAGE)),
        ]);
    }
}
