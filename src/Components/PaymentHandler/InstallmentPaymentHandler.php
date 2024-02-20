<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler;

use Ratepay\RpayPayments\Util\RequestHelper;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class InstallmentPaymentHandler extends AbstractPaymentHandler
{
    use DebitValidationTrait;

    /**
     * @var string
     */
    final public const RATEPAY_METHOD = 'INSTALLMENT';

    /**
     * @return DataValidationDefinition[]
     */
    public function getValidationDefinitions(DataBag $requestDataBag, $baseData): array
    {
        $validations = parent::getValidationDefinitions($requestDataBag, $baseData);

        $installment = new DataValidationDefinition();
        $installment->add(
            'type',
            new NotBlank(),
            new Choice([
                'choices' => ['time', 'rate'],
            ])
        );
        $installment->add(
            'value',
            new NotBlank()
        );

        $installment->add(
            'hash',
            new NotBlank()
        );
        $installment->add(
            'paymentType',
            new NotBlank(),
            new Choice([
                'choices' => ['DIRECT-DEBIT', 'BANK-TRANSFER'],
            ])
        );

        /** @var DataBag $ratepayData */
        $ratepayData = RequestHelper::getRatepayData($requestDataBag);

        $installmentData = $ratepayData->get('installment');
        if ($installmentData->get('paymentType') && $installmentData->get('paymentType') === 'DIRECT-DEBIT') {
            $validations = array_merge($validations, $this->getDebitConstraints($baseData));
        }

        $validations['installment'] = $installment;

        return $validations;
    }

    public static function getRatepayPaymentMethodName(): string
    {
        return self::RATEPAY_METHOD;
    }
}
