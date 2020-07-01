<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Event;


use Ratepay\RatepayPayments\Components\RatepayApi\Dto\IRequestData;
use RatePAY\RequestBuilder;
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


    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return RequestBuilder
     */
    public function getRequestBuilder(): RequestBuilder
    {
        return $this->requestBuilder;
    }

    /**
     * @return IRequestData
     */
    public function getRequestData(): IRequestData
    {
        return $this->requestData;
    }

}
