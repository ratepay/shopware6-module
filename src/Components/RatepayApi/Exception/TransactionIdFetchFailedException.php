<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Exception;

use Ratepay\RpayPayments\Exception\RatepayException;
use Throwable;

class TransactionIdFetchFailedException extends RatepayException
{
    public function __construct($code = 0, Throwable $previous = null)
    {
        parent::__construct("Ratepay wasn't able to fetch a transaction id. So the payment can not complete.", $code, $previous);
    }
}
