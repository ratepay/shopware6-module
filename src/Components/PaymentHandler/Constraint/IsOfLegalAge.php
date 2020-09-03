<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler\Constraint;

use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class IsOfLegalAge extends LessThanOrEqual
{
    public const LEGAL_AGE = 18;

    public const TOO_YOUNG_ERROR_NAME = 'RP_AGE_TO_YOUNG';

    protected static $errorNames = [
        self::TOO_HIGH_ERROR => self::TOO_YOUNG_ERROR_NAME
    ];

    public function __construct($options = null)
    {
        $options = $options ?? [];
        $options['value'] = sprintf('-%d years', self::LEGAL_AGE);
        parent::__construct($options);
    }

    public function validatedBy(): string
    {
        return IsOfLegalAgeValidator::class;
    }
}
