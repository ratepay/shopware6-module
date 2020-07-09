<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Util;


use Ratepay\RatepayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\PrepaymentPaymentHandler;

class MethodHelper
{

    public static function isRatepayMethod(string $handlerIdentifier): bool
    {
        return in_array($handlerIdentifier, [
            DebitPaymentHandler::class,
            InstallmentPaymentHandler::class,
            InstallmentZeroPercentPaymentHandler::class,
            InvoicePaymentHandler::class,
            PrepaymentPaymentHandler::class,
        ], true);
    }

    public static function isInstallmentMethod(string $handlerIdentifier): bool
    {
        return in_array($handlerIdentifier, [
            InstallmentPaymentHandler::class,
            InstallmentZeroPercentPaymentHandler::class,
        ], true);
    }

}
