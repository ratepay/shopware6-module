<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Event;

use RatePAY\RequestBuilder;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Shopware\Core\Framework\Context;
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

    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context, AbstractRequestData $requestData, RequestBuilder $requestBuilder)
    {
        $this->context = $context;
        $this->requestData = $requestData;
        $this->requestBuilder = $requestBuilder;
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
