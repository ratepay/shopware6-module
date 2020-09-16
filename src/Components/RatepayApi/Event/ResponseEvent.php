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

class ResponseEvent extends \Symfony\Contracts\EventDispatcher\Event
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var IRequestData
     */
    private $requestData;

    public function __construct(Context $context, RequestBuilder $requestBuilder, IRequestData $requestData)
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

    public function getRequestData(): IRequestData
    {
        return $this->requestData;
    }
}
