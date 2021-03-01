<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Event;

use RatePAY\RequestBuilder;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Symfony\Contracts\EventDispatcher\Event;

class RequestDoneEvent extends Event
{
    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var AbstractRequestData
     */
    private $requestData;

    public function __construct(AbstractRequestData $requestData, RequestBuilder $requestBuilder)
    {
        $this->requestData = $requestData;
        $this->requestBuilder = $requestBuilder;
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
