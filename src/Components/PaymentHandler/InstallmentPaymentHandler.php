<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler;

use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class InstallmentPaymentHandler extends AbstractPaymentHandler
{
    use DebitValidationTrait;

    public const RATEPAY_METHOD = 'INSTALLMENT';

    public function getValidationDefinitions(Request $request, $baseData): array
    {
        $validations = parent::getValidationDefinitions($request, $baseData);

        $installment = new DataValidationDefinition();
        $installment->add('type',
            new NotBlank(),
            new Choice(['choices' => ['time', 'rate']])
        );
        $installment->add('value',
            new NotBlank()
        );

        $installment->add('hash',
            new NotBlank()
        );
        $installment->add('paymentType',
            new NotBlank(),
            new Choice(['choices' => ['DIRECT-DEBIT', 'BANK-TRANSFER']])
        );

        $ratepayData = $request->get('ratepay');
        if (isset($ratepayData['installment']['paymentType']) &&
            $ratepayData['installment']['paymentType'] === 'DIRECT-DEBIT'
        ) {
            $validations = array_merge($validations, $this->getDebitConstraints($request, $baseData));
        }

        $validations['installment'] = $installment;

        return $validations;
    }
}
