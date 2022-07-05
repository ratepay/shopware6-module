<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\OrderManagement\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class RatepayOrderLineItemsDataDeleteRestrictionException extends ShopwareHttpException
{
    public function __construct()
    {
        parent::__construct('Ratepay data of order line items should not be deleted.');
    }

    public function getErrorCode(): string
    {
        return 'RP_ORDER_MANAGEMENT__ORDER_LINE_ITEMS_DATA_DELETE_RESTRICTION';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
