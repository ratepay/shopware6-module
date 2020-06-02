<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Core\ProfileConfig;

use Shopware\Core\Checkout\Payment\PaymentMethodDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
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
            (new IdField(
                'id',
                ProfileConfigMethodEntity::FIELD_ID
            ))->addFlags(new Required(), new PrimaryKey()),

            (new ManyToOneAssociationField(
                ProfileConfigMethodEntity::FIELD_PROFILE,
                'profile_id',
                ProfileConfigDefinition::class,
                'id',
                false
            ))->addFlags(new CascadeDelete()),

            (new IdField(
                'profile_id',
                ProfileConfigMethodEntity::FIELD_PROFILE_ID
            )),

            (new ManyToOneAssociationField(
                ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD,
                'payment_method_id',
                PaymentMethodDefinition::class,
                'id',
                false
            ))->addFlags(new CascadeDelete()),

            (new IdField(
                'payment_method_id',
                ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD_ID
            ))->addFlags(new Required()/*, new PrimaryKey()*/), // shopware does not support multiple primary keys

            (new FloatField(
                'limit_min',
                ProfileConfigMethodEntity::FIELD_LIMIT_MIN
            )),

            (new FloatField(
                'limit_max',
                ProfileConfigMethodEntity::FIELD_LIMIT_MAX
            )),

            (new FloatField(
                'limit_max_b2b',
                ProfileConfigMethodEntity::FIELD_LIMIT_MAX_B2B
            )),

            (new BoolField(
                'allow_b2b',
                ProfileConfigMethodEntity::FIELD_ALLOW_B2B
            ))->addFlags(new Required()),

            (new BoolField(
                'allow_different_addresses',
                ProfileConfigMethodEntity::FIELD_ALLOW_DIFFERENT_ADDRESSES
            )),

            (new OneToOneAssociationField(
                ProfileConfigMethodEntity::FIELD_INSTALLMENT_CONFIG,
                'id',
                'id',
                ProfileConfigMethodInstallmentDefinition::class,
                true
            ))->addFlags(new CascadeDelete()),
        ]);
    }
}
