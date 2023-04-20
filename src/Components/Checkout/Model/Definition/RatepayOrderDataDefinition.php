<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Model\Definition;

use Ratepay\RpayPayments\Components\Checkout\Model\Collection\RatepayOrderDataCollection;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class RatepayOrderDataDefinition extends EntityDefinition
{
    /**
     * @var string
     */
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
            (new IdField(
                'id',
                RatepayOrderDataEntity::FIELD_ID
            ))->addFlags(new Required(), new PrimaryKey()),

            (new FkField(
                'order_id',
                RatepayOrderDataEntity::FIELD_ORDER_ID,
                OrderDefinition::class
            ))->addFlags(new Required()),
            (new ReferenceVersionField(OrderDefinition::class))->addFlags(new Required()),

            (new StringField(
                'profile_id',
                RatepayOrderDataEntity::FIELD_PROFILE_ID
            ))->addFlags(new Required()),

            (new StringField(
                'transaction_id',
                RatepayOrderDataEntity::FIELD_TRANSACTION_ID
            )),

            (new StringField(
                'descriptor',
                RatepayOrderDataEntity::FIELD_DESCRIPTOR
            )),

            (new FkField(
                'shipping_position_id',
                RatepayOrderDataEntity::FIELD_SHIPPING_POSITION_ID,
                RatepayPositionDefinition::class
            )),

            (new JsonField(
                'additional_data',
                RatepayOrderDataEntity::FIELD_ADDITIONAL_DATA
            )),

            (new BoolField(
                'successful',
                RatepayOrderDataEntity::FIELD_SUCCESSFUL
            )),

            (new BoolField(
                'send_discount_as_cart_item',
                RatepayOrderDataEntity::FIELD_SEND_DISCOUNT_AS_CART_ITEM
            )),

            (new BoolField(
                'send_shipping_costs_as_cart_item',
                RatepayOrderDataEntity::FIELD_SEND_SHIPPING_COSTS_AS_CART_ITEM
            )),

            new OneToOneAssociationField(
                RatepayOrderDataEntity::FIELD_ORDER,
                'order_id',
                'id',
                OrderDefinition::class,
                false
            ),
            new OneToOneAssociationField(
                RatepayOrderDataEntity::FIELD_SHIPPING_POSITION,
                'shipping_position_id',
                'id',
                RatepayPositionDefinition::class,
                true
            ),
        ]);
    }
}
