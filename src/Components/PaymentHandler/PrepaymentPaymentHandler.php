<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler;


use Symfony\Component\Validator\Constraints\Positive;

class PrepaymentPaymentHandler extends AbstractPaymentHandler
{
    const RATEPAY_METHOD = 'PREPAYMENT';

    public function getValidationDefinitions()
    {
        return [
            'day' => [new Positive()],
            'month' => [new Positive()],
            'year' => [new Positive()]
        ];
    }
}
