<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler;


use Shopware\Core\System\SalesChannel\SalesChannelContext;

class InstallmentPaymentHandler extends AbstractPaymentHandler
{

    const RATEPAY_METHOD = 'INSTALLMENT';

    public function getValidationDefinitions(SalesChannelContext $salesChannelContext)
    {
        return parent::getValidationDefinitions($salesChannelContext);
        // TODO implement definitions
    }
}
