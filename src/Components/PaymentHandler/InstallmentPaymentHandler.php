<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler;


use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class InstallmentPaymentHandler extends DebitPaymentHandler
{

    public const RATEPAY_METHOD = 'INSTALLMENT';

    public function getValidationDefinitions(Request $request, SalesChannelContext $salesChannelContext): array
    {
        $validations = parent::getValidationDefinitions($request, $salesChannelContext);

        $installment = new DataValidationDefinition();
        $installment->add('type',
            new NotBlank(['message' => 'ratepay.storefront.checkout.errors.unknownError']),
            new Choice(['choices' => ['time', 'rate'], 'message' => 'ratepay.storefront.checkout.errors.unknownError'])
        );
        $installment->add('value',
            new NotBlank(['message' => 'ratepay.storefront.checkout.errors.unknownError'])
        );

        $installment->add('hash',
            new NotBlank(['message' => 'ratepay.storefront.checkout.errors.unknownError'])
        );
        $installment->add('paymentType',
            new NotBlank(['message' => 'ratepay.storefront.checkout.errors.unknownError']),
            new Choice(['choices' => ['DIRECT-DEBIT', 'BANK-TRANSFER'], 'message' => 'ratepay.storefront.checkout.errors.unknownError'])
        );

        $ratepayData = $request->get('ratepay');
        if (isset($ratepayData['installment']['paymentType']) && $ratepayData['installment']['paymentType'] !== 'DIRECT-DEBIT') {
            unset($validations['bankData']);
        }

        $validations['installment'] = $installment;
        return $validations;
    }
}
