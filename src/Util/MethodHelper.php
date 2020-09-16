<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Util;

use Ratepay\RpayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\PrepaymentPaymentHandler;
use Shopware\Core\Checkout\Order\OrderEntity;

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

    /**
     * checks is the last transaction of an order processed by a ratepay method.
     */
    public static function isRatepayOrder(OrderEntity $order): bool
    {
        $transaction = $order->getTransactions()->last();

        return $transaction &&
            $transaction->getPaymentMethod() &&
            self::isRatepayMethod($transaction->getPaymentMethod()->getHandlerIdentifier());
    }

    public static function isInstallmentMethod(string $handlerIdentifier): bool
    {
        return in_array($handlerIdentifier, [
            InstallmentPaymentHandler::class,
            InstallmentZeroPercentPaymentHandler::class,
        ], true);
    }
}
