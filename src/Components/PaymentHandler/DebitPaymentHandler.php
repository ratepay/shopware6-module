<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\NotBlank;

class DebitPaymentHandler extends AbstractPaymentHandler
{
    const RATEPAY_METHOD = 'ELV';

    public function getValidationDefinitions(SalesChannelContext $salesChannelContext): array
    {
        $validations = parent::getValidationDefinitions($salesChannelContext);

        $validations['accountholder'] = [new NotBlank()]; // Not required, it will be overridden by the customerFactory
        $validations['iban'] = [new NotBlank(), new Iban()];
        $validations['sepaconfirmation'] = [new NotBlank()];

        return $validations;
    }

}
