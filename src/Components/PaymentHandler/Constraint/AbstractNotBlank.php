<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler\Constraint;

use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotBlankValidator;

abstract class AbstractNotBlank extends NotBlank
{
    abstract protected static function getRatepayErrorCode(): string;

    public function __construct(array $options = null)
    {
        $options['message'] = AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . static::getRatepayErrorCode();
        parent::__construct($options);
    }

    public static function getErrorName(string $errorCode): string
    {
        if ($errorCode === static::IS_BLANK_ERROR) {
            return static::getRatepayErrorCode();
        }

        return parent::getErrorName($errorCode);
    }

    public function validatedBy(): string
    {
        return NotBlankValidator::class;
    }
}
