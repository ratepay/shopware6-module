<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;

use RatePAY\RequestBuilder;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentInitData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;

/**
 * @method RequestBuilder doRequest(PaymentInitData $requestData)
 */
class PaymentInitService extends AbstractRequest
{
    public const EVENT_SUCCESSFUL = self::class . parent::EVENT_SUCCESSFUL;

    public const EVENT_FAILED = self::class . parent::EVENT_FAILED;

    public const EVENT_BUILD_HEAD = self::class . parent::EVENT_BUILD_HEAD;

    public const EVENT_BUILD_CONTENT = self::class . parent::EVENT_BUILD_CONTENT;

    protected string $_operation = self::CALL_PAYMENT_INIT;

    protected function supportsRequestData(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof PaymentInitData;
    }
}
