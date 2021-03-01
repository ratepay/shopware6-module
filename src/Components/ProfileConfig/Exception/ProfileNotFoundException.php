<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Exception;

use Ratepay\RpayPayments\Exception\RatepayException;

class ProfileNotFoundException extends RatepayException
{
    public function __construct()
    {
        parent::__construct('Profile-ID was not found. Operation can not executed.');
    }
}
