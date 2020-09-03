<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler\Constraint;

use Symfony\Component\Validator\Constraints\Date;

class Birthday extends Date
{

    public const ERROR_NAME = 'RP_INVALID_AGE';

    protected static $errorNames = [
        self::INVALID_FORMAT_ERROR => self::ERROR_NAME,
        self::INVALID_DATE_ERROR => self::ERROR_NAME,
    ];

    public function validatedBy(): string
    {
        return BirthdayValidator::class;
    }
}
