<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\AdminOrders\Model\Definition;

use Ratepay\RpayPayments\Components\AdminOrders\Model\Collection\RatepayAdminOrderTokenCollection;
use Ratepay\RpayPayments\Components\AdminOrders\Model\RatepayAdminOrderTokenEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class RatepayAdminOrderTokenDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ratepay_admin_order_token';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return RatepayAdminOrderTokenEntity::class;
    }

    public function getCollectionClass(): string
    {
        return RatepayAdminOrderTokenCollection::class;
    }

    /**
     * @return Field[]
     */
    protected function defaultFields(): array
    {
        return [
            new CreatedAtField(),
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', RatepayAdminOrderTokenEntity::FIELD_ID))->addFlags(new Required(), new PrimaryKey()),
            (new StringField('token', RatepayAdminOrderTokenEntity::FIELD_TOKEN))->addFlags(new Required()),

            (new FkField(
                'sales_channel_id',
                RatepayAdminOrderTokenEntity::FIELD_SALES_CHANNEL_ID,
                SalesChannelDefinition::class
            ))->addFlags(new Required()),

            (new FkField(
                'sales_channel_domain_id',
                RatepayAdminOrderTokenEntity::FIELD_SALES_CHANNEL_DOMAIN_ID,
                SalesChannelDomainDefinition::class
            ))->addFlags(new Required()),

            (new StringField('cart_token', RatepayAdminOrderTokenEntity::FIELD_CART_TOKEN)),
            (new DateTimeField('valid_until', RatepayAdminOrderTokenEntity::FIELD_VAlID_UNTIL)),
        ]);
    }
}
