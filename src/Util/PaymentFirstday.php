<?php

namespace Ratepay\RpayPayments\Util;

class PaymentFirstday
{

    public const DIRECT_DEBIT = 2;
    public const BANK_TRANSFER = 28;

    public static function getFirstdayForType(string $type): int
    {
        switch ($type) {
            case 'DIRECT-DEBIT':
                return 2;
            case 'BANK-TRANSFER':
                return 28;
        }

        throw new \RuntimeException('Invalid type :' . $type);
    }

}
