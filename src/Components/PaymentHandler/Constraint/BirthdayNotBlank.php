<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler\Constraint;

class BirthdayNotBlank extends AbstractNotBlank
{
    protected static function getRatepayErrorCode(): string
    {
        return 'RP_INVALID_AGE';
    }
}
