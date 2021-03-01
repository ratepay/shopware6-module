<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler;

use Symfony\Component\HttpFoundation\Request;

class DebitPaymentHandler extends AbstractPaymentHandler
{
    use DebitValidationTrait;

    public const RATEPAY_METHOD = 'ELV';

    public function getValidationDefinitions(Request $request, $baseData): array
    {
        return array_merge(
            parent::getValidationDefinitions($request, $baseData),
            $this->getDebitConstraints($request, $baseData)
        );
    }
}
