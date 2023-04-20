<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Model\Collection;

use Ratepay\RpayPayments\Components\Logging\Model\ApiRequestLogEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<ApiRequestLogEntity>
 */
class ApiRequestLogCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ApiRequestLogEntity::class;
    }
}
