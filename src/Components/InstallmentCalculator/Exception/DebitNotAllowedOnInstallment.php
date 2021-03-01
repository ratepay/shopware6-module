<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class DebitNotAllowedOnInstallment extends ShopwareHttpException
{
    public function __construct()
    {
        parent::__construct('Debit is not allowed on installment payment');
    }

    public function getErrorCode(): string
    {
        return 'RP_DEBIT_NOT_ALLOWED_ON_INSTALLMENT';
    }
}
