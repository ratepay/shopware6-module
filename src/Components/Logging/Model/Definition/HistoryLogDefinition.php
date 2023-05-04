<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Model\Definition;

use Ratepay\RpayPayments\Components\Logging\Model\Collection\HistoryLogCollection;
use Ratepay\RpayPayments\Components\Logging\Model\HistoryLogEntity;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class HistoryLogDefinition extends EntityDefinition
{
    /**
     * @var string
     */
    final public const ENTITY_NAME = 'ratepay_order_history';

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
            (new FkField('order_id', HistoryLogEntity::FIELD_ORDER_ID, OrderDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(OrderDefinition::class))->addFlags(new Required()),
            (new StringField('event', HistoryLogEntity::FIELD_EVENT)),
            (new StringField('user', HistoryLogEntity::FIELD_USER)),
            (new StringField('product_name', HistoryLogEntity::FIELD_PRODUCT_NAME)),
            (new StringField('product_number', HistoryLogEntity::FIELD_PRODUCT_NUMBER)),
            (new IntField('quantity', HistoryLogEntity::FIELD_QTY)),

            new ManyToOneAssociationField(HistoryLogEntity::FIELD_ORDER, 'order_id', OrderDefinition::class, 'id', false),
        ]);
    }
}
