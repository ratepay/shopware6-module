<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Model\Definition;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\Definition\ProfileConfigDefinition;
use Ratepay\RpayPayments\Components\RatepayApi\Model\Collection\TransactionIdCollection;
use Ratepay\RpayPayments\Components\RatepayApi\Model\TransactionIdEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class TransactionIdEntityDefinition extends EntityDefinition
{
    /**
     * @var string
     */
    final public const ENTITY_NAME = 'ratepay_transaction_id_temp';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return TransactionIdEntity::class;
    }

    public function getCollectionClass(): string
    {
        return TransactionIdCollection::class;
    }

    protected function defaultFields(): array
    {
        return [
            new CreatedAtField(),
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField(
                'id',
                TransactionIdEntity::FIELD_ID
            ))->addFlags(new Required(), new PrimaryKey()),

            (new StringField(
                'identifier',
                TransactionIdEntity::FIELD_IDENTIFIER
            ))->addFlags(new Required()),

            (new FkField(
                'profile_id',
                TransactionIdEntity::FIELD_PROFILE_ID,
                ProfileConfigDefinition::class,
                'id'
            ))->addFlags(new Required()),

            (new StringField(
                'transaction_id',
                TransactionIdEntity::FIELD_TRANSACTION_ID
            ))->addFlags(new Required()),

            (new ManyToOneAssociationField(
                TransactionIdEntity::FIELD_PROFILE,
                'profile_id',
                ProfileConfigDefinition::class,
                'id',
                false
            )),
        ]);
    }
}
