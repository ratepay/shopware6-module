<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentLock\Model\Definition;

use Ratepay\RpayPayments\Components\PaymentLock\Model\Collection\PaymentLockCollection;
use Ratepay\RpayPayments\Components\PaymentLock\Model\PaymentLockEntity;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Checkout\Payment\PaymentMethodDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class PaymentLockDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'ratepay_payment_lock';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return PaymentLockEntity::class;
    }

    public function getCollectionClass(): string
    {
        return PaymentLockCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField(
                'id',
                PaymentLockEntity::FIELD_ID
            ))->addFlags(new Required(), new PrimaryKey()),

            (new FkField(
                'customer_id',
                PaymentLockEntity::FIELD_CUSTOMER_ID,
                CustomerDefinition::class
            ))->addFlags(new Required()),

            (new FkField(
                'payment_method_id',
                PaymentLockEntity::FIELD_PAYMENT_METHOD_ID,
                PaymentMethodDefinition::class
            ))->setFlags(new Required()),

            (new DateTimeField(
                'locked_until',
                PaymentLockEntity::FIELD_LOCKED_UNTIL
            ))->setFlags(new Required()),
        ]);
    }
}
