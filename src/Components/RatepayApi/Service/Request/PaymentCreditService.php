<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;

class PaymentCreditService extends AbstractModifyRequest
{
    public const EVENT_SUCCESSFUL = self::class . parent::EVENT_SUCCESSFUL;

    public const EVENT_FAILED = self::class . parent::EVENT_FAILED;

    public const EVENT_BUILD_HEAD = self::class . parent::EVENT_BUILD_HEAD;

    public const EVENT_BUILD_CONTENT = self::class . parent::EVENT_BUILD_CONTENT;

    protected $_subType = 'credit';
}
