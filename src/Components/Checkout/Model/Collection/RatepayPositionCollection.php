<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Model\Collection;


use Ratepay\RpayPayments\Components\Checkout\Model\RatepayPositionEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(RatepayPositionEntity $entity)
 * @method void set(string $key, RatepayPositionEntity $entity)
 * @method RatepayPositionEntity[] getIterator()
 * @method RatepayPositionEntity[] getElements()
 * @method RatepayPositionEntity|null get(string $key)
 * @method RatepayPositionEntity|null first()
 * @method RatepayPositionEntity|null last()
 */
class RatepayPositionCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return RatepayPositionEntity::class;
    }
}
