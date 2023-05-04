<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Model\Definition;

use Ratepay\RpayPayments\Components\Checkout\Model\Collection\RatepayPositionCollection;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayPositionEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class RatepayPositionDefinition extends EntityDefinition
{
    /**
     * @var string
     */
    final public const ENTITY_NAME = 'ratepay_position';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return RatepayPositionEntity::class;
    }

    public function getCollectionClass(): string
    {
        return RatepayPositionCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', RatepayPositionEntity::FIELD_ID))->addFlags(new Required(), new PrimaryKey()),
            (new IntField('canceled', RatepayPositionEntity::FIELD_CANCELED)),
            (new IntField('returned', RatepayPositionEntity::FIELD_RETURNED)),
            (new IntField('delivered', RatepayPositionEntity::FIELD_DELIVERED)),
        ]);
    }
}
