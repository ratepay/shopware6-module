<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Logging\Model\Collection;


use Ratepay\RatepayPayments\Components\Logging\Model\ApiRequestLogEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void add(ApiRequestLogEntity $entity)
 * @method void set(string $key, ApiRequestLogEntity $entity)
 * @method ApiRequestLogEntity[] getIterator()
 * @method ApiRequestLogEntity[] getElements()
 * @method ApiRequestLogEntity|null get(string $key)
 * @method ApiRequestLogEntity|null first()
 * @method ApiRequestLogEntity|null last()
 */
class ApiRequestLogCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ApiRequestLogEntity::class;
    }
}
