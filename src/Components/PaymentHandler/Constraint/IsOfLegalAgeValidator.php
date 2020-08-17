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

class IsOfLegalAgeValidator extends LessThanOrEqualValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsOfLegalAge) {
            throw new UnexpectedTypeException($constraint, IsOfLegalAge::class);
        }

        if (!is_array($value) || !isset($value['year'], $value['month'], $value['day'])) {
            throw new UnexpectedTypeException(getType($value), 'array');
        }

        parent::validate((new DateTime())->setDate($value['year'], $value['month'], $value['day']), $constraint);
    }
}
