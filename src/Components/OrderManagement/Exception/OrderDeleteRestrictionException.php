<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\OrderManagement\Exception;

use Shopware\Core\Framework\ShopwareHttpException;
use Symfony\Component\HttpFoundation\Response;

class OrderDeleteRestrictionException extends ShopwareHttpException
{
    public function __construct()
    {
        parent::__construct('Orders payed with Ratepay payment methods can not be deleted via API.');
    }

    public function getErrorCode(): string
    {
        return 'RP_ORDER_MANAGEMENT__ORDER_DELETE_RESTRICTION';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
