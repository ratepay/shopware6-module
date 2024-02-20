<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler;

use Ratepay\RpayPayments\Components\Checkout\Util\BankAccountHolderHelper;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\BankAccountHolderChoice;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\BankAccountHolderNotBlank;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\Iban;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\IbanNotBlank;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\SepaConfirmNotBlank;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

trait DebitValidationTrait
{
    public function getDebitConstraints(SalesChannelContext|OrderEntity $baseData): array
    {
        $bankData = new DataValidationDefinition();
        $bankData->add('accountHolder', new BankAccountHolderNotBlank(), new BankAccountHolderChoice(
            BankAccountHolderHelper::getAvailableNames($baseData)
        ));
        $bankData->add(
            'iban',
            new IbanNotBlank(),
            new Iban()
        );
        $bankData->add(
            'sepaConfirmation',
            new SepaConfirmNotBlank()
        );

        return [
            'bankData' => $bankData,
        ];
    }
}
