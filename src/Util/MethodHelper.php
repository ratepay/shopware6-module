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
     * checks is the last transaction of an order processed by a ratepay method
     * @param OrderEntity $order
     * @return bool
     */
    public static function isRatepayOrder(OrderEntity $order): bool
    {
        return ($transaction = $order->getTransactions()->last()) &&
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
