<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler;

use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\Iban;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\IbanNotBlank;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\SepaConfirmNotBlank;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

trait DebitValidationTrait
{
    /**
     * @param OrderEntity|SalesChannelContext $baseData
     */
    public function getDebitConstraints(Request $request, $baseData): array
    {
        $bankData = new DataValidationDefinition();
        //$bankData->add('accountHolder', new NotBlank()); // Not required, it will be overridden by the customerFactory
        $bankData->add('iban',
            new IbanNotBlank(),
            new Iban()
        );
        $bankData->add('sepaConfirmation',
            new SepaConfirmNotBlank()
        );

        return [
            'bankData' => $bankData,
        ];
    }
}
