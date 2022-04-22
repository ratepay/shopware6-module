<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Model\Definition;

use Ratepay\RpayPayments\Components\Logging\Model\ApiRequestLogEntity;
use Ratepay\RpayPayments\Components\Logging\Model\Collection\ApiRequestLogCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
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

    protected function defaultFields(): array
    {
        return [
            new CreatedAtField(),
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', ApiRequestLogEntity::FIELD_ID))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('version', ApiRequestLogEntity::FIELD_VERSION))->addFlags(new Required()),
            (new StringField('operation', ApiRequestLogEntity::FIELD_OPERATION))->addFlags(new Required()),
            (new StringField('sub_operation', ApiRequestLogEntity::FIELD_SUB_OPERATION)),

            (new StringField('result_code', ApiRequestLogEntity::FIELD_RESULT_CODE)),
            (new StringField('result_text', ApiRequestLogEntity::FIELD_RESULT_TEXT)),
            (new StringField('status_code', ApiRequestLogEntity::FIELD_STATUS_CODE)),
            (new StringField('status_text', ApiRequestLogEntity::FIELD_STATUS_TEXT)),
            (new StringField('reason_code', ApiRequestLogEntity::FIELD_REASON_CODE)),
            (new StringField('reason_text', ApiRequestLogEntity::FIELD_REASON_TEXT)),

            (new LongTextField('request', ApiRequestLogEntity::FIELD_REQUEST))->addFlags(new Required(), new AllowHtml()),
            (new LongTextField('response', ApiRequestLogEntity::FIELD_RESPONSE))->addFlags(new Required(), new AllowHtml()),
            (new JsonField('additional_data', ApiRequestLogEntity::FIELD_ADDITIONAL_DATA)),
        ]);
    }
}
