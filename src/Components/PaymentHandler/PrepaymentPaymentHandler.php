<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler;

class PrepaymentPaymentHandler extends AbstractPaymentHandler
{
    /**
     * @var string
     */
    public const RATEPAY_METHOD = 'PREPAYMENT';

    public static function getRatepayPaymentMethodName(): string
    {
        return self::RATEPAY_METHOD;
    }
}
