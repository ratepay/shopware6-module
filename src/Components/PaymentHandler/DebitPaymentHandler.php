<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\Positive;

class DebitPaymentHandler extends AbstractPaymentHandler
{
    const RATEPAY_METHOD = 'ELV';

    public function getValidationDefinitions(): array
    {
        return [
            'day' => [new Positive()],
            'month' => [new Positive()],
            'year' => [new Positive()],
            'accountholder' => [new NotBlank()],
            'sepaconfirmation' => [new NotBlank()],
            'iban' => [new NotBlank(), new Iban()]
        ];
    }
}
