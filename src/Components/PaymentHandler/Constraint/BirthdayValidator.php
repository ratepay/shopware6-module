<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\DateValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class BirthdayValidator extends DateValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Birthday) {
            throw new UnexpectedTypeException($constraint, Birthday::class);
        }

        if (!is_array($value) || !isset($value['year'], $value['month'], $value['day'])) {
            throw new UnexpectedTypeException(getType($value), 'array');
        }

        parent::validate(sprintf('%d-%02d-%02d', $value['year'], $value['month'], $value['day']), $constraint);
    }
}
