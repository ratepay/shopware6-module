<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler\Constraint;

use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Symfony\Component\Validator\Constraints\IbanValidator;

class Iban extends \Symfony\Component\Validator\Constraints\Iban
{
    protected static $errorNames = [
        self::INVALID_COUNTRY_CODE_ERROR => 'RP_INVALID_COUNTRY_CODE_ERROR',
        self::INVALID_CHARACTERS_ERROR => 'RP_INVALID_CHARACTERS_ERROR',
        self::CHECKSUM_FAILED_ERROR => 'RP_CHECKSUM_FAILED_ERROR',
        self::INVALID_FORMAT_ERROR => 'RP_INVALID_FORMAT_ERROR',
        self::NOT_SUPPORTED_COUNTRY_CODE_ERROR => 'RP_NOT_SUPPORTED_COUNTRY_CODE_ERROR',
    ];

    public $message = AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . 'RP_MISSING_IBAN';

    public function validatedBy(): string
    {
        return IbanValidator::class;
    }
}
