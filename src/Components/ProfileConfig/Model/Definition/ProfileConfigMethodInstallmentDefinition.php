<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Model\Definition;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\Collection\ProfileConfigMethodInstallmentCollection;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodInstallmentEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ListField;
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
        return ProfileConfigMethodInstallmentEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProfileConfigMethodInstallmentCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField(
                'id',
                ProfileConfigMethodInstallmentEntity::FIELD_ID
            ))->addFlags(new Required(), new PrimaryKey()),

            (new ListField(
                'month_allowed',
                ProfileConfigMethodInstallmentEntity::FIELD_ALLOWED_MONTHS,
                IntField::class
            ))->addFlags(new Required()),

            (new BoolField(
                'is_banktransfer_allowed',
                ProfileConfigMethodInstallmentEntity::FIELD_IS_BANKTRANSFER_ALLOWED
            ))->addFlags(new Required()),

            (new BoolField(
                'is_debit_allowed',
                ProfileConfigMethodInstallmentEntity::FIELD_IS_DEBIT_ALLOWED
            ))->addFlags(new Required()),

            (new FloatField(
                'rate_min_normal',
                ProfileConfigMethodInstallmentEntity::FIELD_RATE_MIN
            ))->addFlags(new Required()),
        ]);
    }
}
