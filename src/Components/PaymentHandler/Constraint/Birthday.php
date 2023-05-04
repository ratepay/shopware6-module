<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler\Constraint;

use Symfony\Component\Validator\Constraints\Date;

class Birthday extends Date
{
    /**
     * @var string
     */
    final public const ERROR_NAME = 'RP_INVALID_AGE';

    /**
     * @var array<string, string>
     */
    protected const ERROR_NAMES = [
        self::INVALID_FORMAT_ERROR => self::ERROR_NAME,
        self::INVALID_DATE_ERROR => self::ERROR_NAME,
    ];

    /**
     * @var string[]
     * @deprecated since Symfony 6.1, use const ERROR_NAMES instead
     */
    protected static $errorNames = self::ERROR_NAMES;

    public function validatedBy(): string
    {
        return BirthdayValidator::class;
    }
}
