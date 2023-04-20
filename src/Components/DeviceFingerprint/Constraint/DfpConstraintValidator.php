<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\DeviceFingerprint\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DfpConstraintValidator extends ConstraintValidator
{
    /**
     * @param DfpConstraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint->getDfpService()->isDfpIdValid($constraint->getObject(), $value)) {
            $this->context->buildViolation('Provided DFP Token is not valid.')
                ->setCode(DfpConstraint::ERROR_CODE)
                ->addViolation();
        }
    }
}
