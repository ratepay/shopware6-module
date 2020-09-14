<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class DebitPaymentHandler extends AbstractPaymentHandler
{
    use DebitValidationTrait;

    public const RATEPAY_METHOD = 'ELV';

    public function getValidationDefinitions(Request $request, SalesChannelContext $salesChannelContext): array
    {
        $validations = parent::getValidationDefinitions($request, $salesChannelContext);
        return array_merge($validations, $this->getDebitConstraints($request, $salesChannelContext));
    }

}
