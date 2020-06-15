<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


class PaymentDebitService extends AbstractAddRequest
{

    protected $_subType = 'credit'; // it is a little bit confusing, but it is correct ;-)

    protected $eventName = 'debit';

    public function setAmount(string $label, float $amount): void
    {
        parent::setAmount($label, $amount < 0 ? $amount * -1 : $amount);
    }
}
