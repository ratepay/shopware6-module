<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Model\Collection;

use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderLineItemDataEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<RatepayOrderLineItemDataEntity>
 */
class RatepayOrderLineItemDataCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return RatepayOrderLineItemDataEntity::class;
    }
}
