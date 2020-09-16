<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Event;

use RatePAY\RequestBuilder;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\IRequestData;
use Shopware\Core\Framework\Context;
use Symfony\Contracts\EventDispatcher\Event;

class RequestDoneEvent extends Event
{
    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var IRequestData
     */
    private $requestData;

    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context, IRequestData $requestData, RequestBuilder $requestBuilder)
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

    public function getRequestData(): IRequestData
    {
        return $this->requestData;
    }
}
