<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


class PaymentReturnService extends AbstractModifyRequest
{

    const EVENT_SUCCESSFUL = self::class . parent::EVENT_SUCCESSFUL;
    const EVENT_FAILED = self::class . parent::EVENT_FAILED;

    protected $_subType = 'return';
}
