<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler\Constraint;

use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\LessThanOrEqualValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class BirthdayConstraintValidator extends LessThanOrEqualValidator
{

    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof BirthdayConstraint) {
            throw new UnexpectedTypeException($constraint, BirthdayConstraint::class);
        }

        if (!is_array($value) || !isset($value['year'], $value['month'], $value['day'])) {
            throw new UnexpectedTypeException(getType($value), 'array');
        }

        parent::validate((new DateTime())->setDate($value['year'], $value['month'], $value['day']), $constraint);

    }
}
