<?php

namespace Ratepay\RpayPayments\Util;

use RuntimeException;
class PaymentFirstday
{

    /**
     * @var int
     */
    public const DIRECT_DEBIT = 2;

    /**
     * @var int
     */
    public const BANK_TRANSFER = 28;

    public static function getFirstdayForType(string $type): int
    {
        if ($type == 'DIRECT-DEBIT') {
            return 2;
        } elseif ($type == 'BANK-TRANSFER') {
            return 28;
        }

        throw new RuntimeException('Invalid type :' . $type);
    }

}
