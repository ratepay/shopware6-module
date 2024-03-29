<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\OrderManagement\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class TaxRuleNotFoundException extends ShopwareHttpException
{
    public function __construct()
    {
        parent::__construct('No suitable tax rule can be found for the current order. Please select another tax-rule or check the selected tax-rule within the store settings.');
    }

    public function getErrorCode(): string
    {
        return 'RP_TAX_RULE_NOT_FOUND';
    }

    public function getStatusCode(): int
    {
        return 400;
    }
}
