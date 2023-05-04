<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Model\Definition;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\Collection\ProfileConfigCollection;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ListField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class ProfileConfigDefinition extends EntityDefinition
{
    /**
     * @var string
     */
    final public const ENTITY_NAME = 'ratepay_profile_config';

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
        return new FieldCollection([
            (new IdField(
                'id',
                ProfileConfigEntity::FIELD_ID
            ))->addFlags(new Required(), new PrimaryKey()),

            (new StringField(
                'profile_id',
                ProfileConfigEntity::FIELD_PROFILE_ID
            ))->addFlags(new Required()),

            (new StringField(
                'security_code',
                ProfileConfigEntity::FIELD_SECURITY_CODE
            ))->addFlags(new Required()),

            (new BoolField(
                'sandbox',
                ProfileConfigEntity::FIELD_SANDBOX
            ))->addFlags(new Required()/* , new PrimaryKey() */),

            (new BoolField(
                'only_admin_orders',
                ProfileConfigEntity::FIELD_ONLY_ADMIN_ORDERS
            ))->addFlags(new Required()/* , new PrimaryKey() */),

            (new OneToOneAssociationField(
                ProfileConfigEntity::FIELD_SALES_CHANNEL,
                'sales_channel_id',
                'id',
                SalesChannelDefinition::class,
                false
            ))->addFlags(new CascadeDelete()),

            (new FkField(
                'sales_channel_id',
                ProfileConfigEntity::FIELD_SALES_CHANNEL_ID,
                SalesChannelDefinition::class
            ))->addFlags(new Required()),

            (new ListField(
                'country_code_billing',
                ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING,
                StringField::class
            )),

            (new ListField(
                'country_code_delivery',
                ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING,
                StringField::class
            )),

            (new ListField(
                'currency',
                ProfileConfigEntity::FIELD_CURRENCY,
                StringField::class
            )),

            (new BoolField(
                'status',
                ProfileConfigEntity::FIELD_STATUS
            )),

            (new StringField(
                'status_message',
                ProfileConfigEntity::FIELD_STATUS_MESSAGE
            )),

            (new OneToManyAssociationField(
                ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS,
                ProfileConfigMethodDefinition::class,
                'profile_id',
                ProfileConfigEntity::FIELD_ID
            )),
        ]);
    }
}
