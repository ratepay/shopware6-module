<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Model\Definition;


use Ratepay\RatepayPayments\Components\Checkout\Model\Collection\RatepayOrderLineItemDataCollection;
use Ratepay\RatepayPayments\Components\Checkout\Model\RatepayOrderLineItemDataEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
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
            (new IdField('id', RatepayOrderLineItemDataEntity::FIELD_ID))->addFlags(new Required(), new PrimaryKey()),
            (new FkField('position_id', RatepayOrderLineItemDataEntity::FIELD_POSITION_ID, RatepayPositionDefinition::class))->addFlags(new Required()),

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
