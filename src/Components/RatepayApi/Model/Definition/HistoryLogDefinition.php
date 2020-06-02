<?php

namespace Ratepay\RatepayPayments\Components\RatepayApi\Model\Definition;

use Ratepay\RatepayPayments\Components\RatepayApi\Model\Collection\HistoryLogCollection;
use Ratepay\RatepayPayments\Components\RatepayApi\Model\HistoryLogEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class HistoryLogDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ratepay_order_history';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return HistoryLogEntity::class;
    }

    public function getCollectionClass(): string
    {
        return HistoryLogCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('orderId', 'orderId'))->addFlags(new Required()),
            (new StringField('event', 'event')),
            (new StringField('articlename', 'articlename')),
            (new StringField('articlenumber', 'articlenumber')),
            (new IntField('quantity', 'quantity'))
        ]);
    }
}
