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
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\NotBlank;

class DebitPaymentHandler extends AbstractPaymentHandler
{
    public const RATEPAY_METHOD = 'ELV';

    public function getValidationDefinitions(Request $request, SalesChannelContext $salesChannelContext): array
    {
        $validations = parent::getValidationDefinitions($request, $salesChannelContext);

        $bankData = new DataValidationDefinition();
        //$bankData->add('accountHolder', new NotBlank()); // Not required, it will be overridden by the customerFactory
        $bankData->add('iban',
            new NotBlank(['message' => 'ratepay.storefront.checkout.errors.missingIban']),
            new Iban(['message' => 'ratepay.storefront.checkout.errors.missingIban'])
        );
        $bankData->add('sepaConfirmation',
            new NotBlank(['message' => 'ratepay.storefront.checkout.errors.missingSepaConfirm'])
        );
        $validations['bankData'] = $bankData;
        return $validations;
    }

}
