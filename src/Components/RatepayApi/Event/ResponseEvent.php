<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Event;

use RatePAY\RequestBuilder;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Shopware\Core\Framework\Context;
use Symfony\Contracts\EventDispatcher\Event;

class ResponseEvent extends Event
{
    private Context $context;

    private RequestBuilder $requestBuilder;

    private AbstractRequestData $requestData;

    public function __construct(Context $context, RequestBuilder $requestBuilder, AbstractRequestData $requestData)
    {
        $this->context = $context;
        $this->requestBuilder = $requestBuilder;
        $this->requestData = $requestData;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getRequestBuilder(): RequestBuilder
    {
        return $this->requestBuilder;
    }

    public function getRequestData(): AbstractRequestData
    {
        return $this->requestData;
    }
}
