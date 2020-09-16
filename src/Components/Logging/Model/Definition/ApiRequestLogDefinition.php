<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Model\Definition;

use Ratepay\RpayPayments\Components\Logging\Model\ApiRequestLogEntity;
use Ratepay\RpayPayments\Components\Logging\Model\Collection\ApiRequestLogCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ApiRequestLogDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ratepay_api_log';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ApiRequestLogEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ApiRequestLogCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', ApiRequestLogEntity::FIELD_ID))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('version', ApiRequestLogEntity::FIELD_VERSION))->addFlags(new Required()),
            (new StringField('operation', ApiRequestLogEntity::FIELD_OPERATION))->addFlags(new Required()),
            (new StringField('sub_operation', ApiRequestLogEntity::FIELD_SUB_OPERATION)),
            (new StringField('result', ApiRequestLogEntity::FIELD_RESULT)),
            (new LongTextField('request', ApiRequestLogEntity::FIELD_REQUEST))->addFlags(new Required(), new AllowHtml()),
            (new LongTextField('response', ApiRequestLogEntity::FIELD_RESPONSE))->addFlags(new Required(), new AllowHtml()),
            (new JsonField('additional_data', ApiRequestLogEntity::FIELD_ADDITIONAL_DATA)),
        ]);
    }
}
