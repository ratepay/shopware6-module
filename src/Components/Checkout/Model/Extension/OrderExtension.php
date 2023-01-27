<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Model\Extension;

use Ratepay\RpayPayments\Components\Checkout\Model\Definition\RatepayOrderDataDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class OrderExtension extends EntityExtension
{
    /**
     * @var string
     */
    public const EXTENSION_NAME = 'ratepayData';

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToOneAssociationField(
                self::EXTENSION_NAME,
                'id',
                'order_id',
                RatepayOrderDataDefinition::class,
                true
            ))->addFlags(new RestrictDelete())
        );
    }

    public function getDefinitionClass(): string
    {
        return OrderDefinition::class;
    }
}
