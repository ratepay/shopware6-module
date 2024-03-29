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
use Symfony\Component\HttpFoundation\Response;

class OrderLineItemDeleteRestrictionException extends ShopwareHttpException
{
    public function __construct()
    {
        parent::__construct('Order line items from orders payed with Ratepay payment methods can not be deleted via API.');
    }

    public function getErrorCode(): string
    {
        return 'RP_ORDER_MANAGEMENT__ORDER_LINE_ITEM_DELETE_RESTRICTION';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
