<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Model\Collection;


use Ratepay\RatepayPayments\Components\Checkout\Model\RatepayOrderLineItemDataEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(RatepayOrderLineItemDataEntity $entity)
 * @method void set(string $key, RatepayOrderLineItemDataEntity $entity)
 * @method RatepayOrderLineItemDataEntity[] getIterator()
 * @method RatepayOrderLineItemDataEntity[] getElements()
 * @method RatepayOrderLineItemDataEntity|null get(string $key)
 * @method RatepayOrderLineItemDataEntity|null first()
 * @method RatepayOrderLineItemDataEntity|null last()
 */
class RatepayOrderLineItemDataCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return RatepayOrderLineItemDataEntity::class;
    }
}
