<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Model\Definition;

use Ratepay\RpayPayments\Components\Checkout\Model\Collection\RatepayOrderLineItemDataCollection;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderLineItemDataEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class RatepayOrderLineItemDataDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ratepay_order_line_item_data';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return RatepayOrderLineItemDataEntity::class;
    }

    public function getCollectionClass(): string
    {
        return RatepayOrderLineItemDataCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField(
                'id',
                RatepayOrderLineItemDataEntity::FIELD_ID
            ))->addFlags(new Required(), new PrimaryKey()),

            (new FkField(
                'order_line_item_id',
                RatepayOrderLineItemDataEntity::FIELD_ORDER_LINE_ITEM_ID,
                OrderLineItemDefinition::class
            ))->addFlags(new Required()),
            (new ReferenceVersionField(OrderLineItemDefinition::class))->addFlags(new Required()),

            (new FkField(
                'position_id',
                RatepayOrderLineItemDataEntity::FIELD_POSITION_ID,
                RatepayPositionDefinition::class
            ))->addFlags(new Required()),

            new OneToOneAssociationField(
                RatepayOrderLineItemDataEntity::FIELD_ORDER_LINE_ITEM,
                'order_line_item_id',
                'id',
                OrderLineItemDefinition::class,
                false
            ),
            new OneToOneAssociationField(
                RatepayOrderLineItemDataEntity::FIELD_POSITION,
                'position_id',
                'id',
                RatepayPositionDefinition::class,
                true
            ),
        ]);
    }
}
