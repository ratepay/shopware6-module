<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler;

class InstallmentZeroPercentPaymentHandler extends AbstractPaymentHandler
{
    /**
     * @var string
     */
    final public const RATEPAY_METHOD = 'INSTALLMENT';

    public static function getRatepayPaymentMethodName(): string
    {
        return self::RATEPAY_METHOD;
    }
}
