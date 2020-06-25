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
