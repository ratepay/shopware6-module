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
use Symfony\Component\Validator\Constraints\IbanValidator;

class Iban extends \Symfony\Component\Validator\Constraints\Iban
{
    /**
     * @var array<string, string>
     */
    protected const ERROR_NAMES = [
        self::INVALID_COUNTRY_CODE_ERROR => 'RP_INVALID_COUNTRY_CODE_ERROR',
        self::INVALID_CHARACTERS_ERROR => 'RP_INVALID_CHARACTERS_ERROR',
        self::CHECKSUM_FAILED_ERROR => 'RP_CHECKSUM_FAILED_ERROR',
        self::INVALID_FORMAT_ERROR => 'RP_INVALID_FORMAT_ERROR',
        self::NOT_SUPPORTED_COUNTRY_CODE_ERROR => 'RP_NOT_SUPPORTED_COUNTRY_CODE_ERROR',
    ];

    /**
     * @var string[]
     * @deprecated since Symfony 6.1, use const ERROR_NAMES instead
     */
    protected static $errorNames = self::ERROR_NAMES;

    public function __construct(?array $options = null, ?string $message = null, ?array $groups = null, mixed $payload = null)
    {
        // we do override the property to avoid backward compatibility (type declaration is missing in lower symfony versions)
        $this->message = AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . 'RP_MISSING_IBAN';

        parent::__construct($options, $message, $groups, $payload);
    }

    public function validatedBy(): string
    {
        return IbanValidator::class;
    }
}
