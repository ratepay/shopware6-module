<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler\Constraint;

use Symfony\Component\Validator\Constraints\LessThanOrEqual;

class BirthdayConstraint extends LessThanOrEqual
{
    public function validatedBy()
    {
        return BirthdayConstraintValidator::class;
    }
}
