<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\InstallmentCalculator\Util;


use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;

class MethodHelper
{

    public static function isInstallmentMethod(string $handlerIdentifier): bool
    {
        return in_array($handlerIdentifier, [
            InstallmentPaymentHandler::class,
            InstallmentZeroPercentPaymentHandler::class,
        ], true);
    }

}
