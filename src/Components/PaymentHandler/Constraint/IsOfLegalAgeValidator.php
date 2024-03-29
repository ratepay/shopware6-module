<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler\Constraint;

use DateTime;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\LessThanOrEqualValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IsOfLegalAgeValidator extends LessThanOrEqualValidator
{
    /**
     * @param mixed $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsOfLegalAge) {
            throw new UnexpectedTypeException($constraint, IsOfLegalAge::class);
        }

        if ($value) {
            if (!is_array($value) || !isset($value['year'], $value['month'], $value['day'])) {
                throw new UnexpectedValueException($value, 'array');
            }

            parent::validate((new DateTime())->setDate((int) $value['year'], (int) $value['month'], (int) $value['day']), $constraint);
        }
    }
}
