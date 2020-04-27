<?php


namespace RatePay\RatePayPayments\Core\RatePayApi;


use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
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
            (new StringField('sub_operation', 'subOperation')),
            (new StringField('transaction_id', 'transactionId')),
            (new StringField('firstname', 'firstname')),
            (new StringField('lastname', 'lastname')),
            (new LongTextField('request', 'request'))->addFlags(new Required()),
            (new LongTextField('response', 'response'))->addFlags(new Required()),
        ]);
    }
}
