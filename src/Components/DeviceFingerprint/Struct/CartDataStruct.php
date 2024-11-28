<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\DeviceFingerprint\Struct;

use Shopware\Core\Framework\Struct\Struct;

class CartDataStruct extends Struct
{
    public function __construct(
        public readonly string $uuid,
        public ?string $hash = null
    ) {
    }
}
