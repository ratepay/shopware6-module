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
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('profile_id', 'profileId'))->addFlags(new Required()),
            (new StringField('security_code', 'securityCode'))->addFlags(new Required()),

            (new OneToOneAssociationField('salesChannel', 'sales_channel_id', 'id', SalesChannelDefinition::class))->addFlags(new CascadeDelete()),
            (new IdField('sales_channel_id', 'salesChannelId')),

            //(new BoolField('zero_percent_installment', 'zeroPercentInstallment'))->addFlags(new Required()),
            (new BoolField('zero_percent_installment', 'zeroPercentInstallment'))->addFlags(new Required()),
            (new StringField('country_code_billing', 'countryCodeBilling')),
            (new StringField('country_code_delivery', 'countryCodeDelivery')),
            (new StringField('currency', 'currency')),

            //payment configs
            //(new OneToOneAssociationField(''))

            //(new StringField('error_default', 'errorDefault')),
            (new BoolField('sandbox', 'sandbox'))->addFlags(new Required()/*, new PrimaryKey()*/),

            (new BoolField('status', 'status')),
            (new StringField('status_message', 'statusMessage')),
        ]);
    }
}
