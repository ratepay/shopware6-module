<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Logging\Model\Definition;

use Ratepay\RatepayPayments\Components\Logging\Model\Collection\HistoryLogCollection;
use Ratepay\RatepayPayments\Components\Logging\Model\HistoryLogEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
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
            (new IdField('id', HistoryLogEntity::FIELD_ID))->addFlags(new Required(), new PrimaryKey()),
            (new IdField('order_id', HistoryLogEntity::FIELD_ORDER_ID))->addFlags(new Required()),
            (new StringField('event', HistoryLogEntity::FIELD_EVENT)),
            (new StringField('user', HistoryLogEntity::FIELD_USER)),
            (new StringField('product_name', HistoryLogEntity::FIELD_PRODUCT_NAME)),
            (new StringField('product_number', HistoryLogEntity::FIELD_PRODUCT_NUMBER)),
            (new IntField('quantity', HistoryLogEntity::FIELD_QTY))
        ]);
    }
}
