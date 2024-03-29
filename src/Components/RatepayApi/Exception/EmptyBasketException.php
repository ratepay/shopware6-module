<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Exception;

use Shopware\Core\Framework\ShopwareHttpException;

class EmptyBasketException extends ShopwareHttpException
{
    public function __construct()
    {
        parent::__construct('Shopping basket should not empty. Please provide items to sent to gateway');
    }

    public function getErrorCode(): string
    {
        return 'RP_SHOPPING_BASKET_IS_EMPTY';
    }
}
