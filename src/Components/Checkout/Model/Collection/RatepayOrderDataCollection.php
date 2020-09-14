<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Model\Collection;


use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(RatepayOrderDataEntity $entity)
 * @method void set(string $key, RatepayOrderDataEntity $entity)
 * @method RatepayOrderDataEntity[] getIterator()
 * @method RatepayOrderDataEntity[] getElements()
 * @method RatepayOrderDataEntity|null get(string $key)
 * @method RatepayOrderDataEntity|null first()
 * @method RatepayOrderDataEntity|null last()
 */
class RatepayOrderDataCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return RatepayOrderDataEntity::class;
    }
}
