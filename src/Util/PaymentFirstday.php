<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Util;

use RuntimeException;

class PaymentFirstday
{
    /**
     * @var int
     */
    final public const DIRECT_DEBIT = 2;

    /**
     * @var int
     */
    final public const BANK_TRANSFER = 28;

    public static function getFirstdayForType(string $type): int
    {
        if ($type === 'DIRECT-DEBIT') {
            return 2;
        } elseif ($type === 'BANK-TRANSFER') {
            return 28;
        }

        throw new RuntimeException('Invalid type :' . $type);
    }
}
