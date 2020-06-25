<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Service\Request;


class PaymentCancelService extends AbstractModifyRequest
{
    public const EVENT_SUCCESSFUL = self::class . parent::EVENT_SUCCESSFUL;
    public const EVENT_FAILED = self::class . parent::EVENT_FAILED;
    public const EVENT_BUILD_HEAD = self::class . parent::EVENT_BUILD_HEAD;

    protected $_subType = 'cancellation';
    protected $eventName = 'cancel';

}
