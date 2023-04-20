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
    /**
     * @param int|string $code
     */
    public function __construct($code = '', Throwable $previous = null)
    {
        parent::__construct(
            "Ratepay wasn't able to fetch a transaction id. So the payment can not complete.",
            is_numeric($code) ? (int) $code : 0,
            $previous
        );
    }
}
