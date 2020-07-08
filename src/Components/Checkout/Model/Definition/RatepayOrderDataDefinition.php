<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Model\Definition;


use Ratepay\RatepayPayments\Components\Checkout\Model\Collection\RatepayOrderDataCollection;
use Ratepay\RatepayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class RatepayOrderDataDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ratepay_order_data';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return RatepayOrderDataEntity::class;
    }

    public function getCollectionClass(): string
    {
        return RatepayOrderDataCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', RatepayOrderDataEntity::FIELD_ID))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('transaction_id', RatepayOrderDataEntity::FIELD_TRANSACTION_ID)),
            (new FkField('shipping_position', RatepayOrderDataEntity::FIELD_SHIPPING_POSITION_ID, RatepayPositionDefinition::class))->addFlags(new Required()),

            new OneToOneAssociationField(
                RatepayOrderDataEntity::FIELD_SHIPPING_POSITION,
                'shipping_position',
                'id',
                RatepayPositionDefinition::class,
                true
            ),
        ]);
    }
}
