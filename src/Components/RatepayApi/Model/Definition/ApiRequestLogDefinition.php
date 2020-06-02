<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Model\Definition;


use Ratepay\RatepayPayments\Components\RatepayApi\Model\ApiRequestLogEntity;
use Ratepay\RatepayPayments\Components\RatepayApi\Model\Collection\ApiRequestLogCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
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
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('version', 'version'))->addFlags(new Required()),
            (new StringField('operation', 'operation'))->addFlags(new Required()),
            (new StringField('suboperation', 'subOperation')),
            (new StringField('status', 'status')),
            (new StringField('transaction_id', 'transactionId')),
            (new StringField('firstname', 'firstname')),
            (new StringField('lastname', 'lastname')),
            (new LongTextField('request', 'request'))->addFlags(new Required(), new AllowHtml()),
            (new LongTextField('response', 'response'))->addFlags(new Required(), new AllowHtml()),
        ]);
    }
}
