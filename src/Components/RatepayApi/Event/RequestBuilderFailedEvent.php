<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Event;

use Exception;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Symfony\Contracts\EventDispatcher\Event;

class RequestBuilderFailedEvent extends Event
{
    /**
     * @var Exception
     */
    protected $exception;

    /**
     * @var AbstractRequestData
     */
    private $requestData;

    public function __construct(Exception $exception, AbstractRequestData $requestData)
    {
        $this->exception = $exception;
        $this->requestData = $requestData;
    }

    public function getException(): Exception
    {
        return $this->exception;
    }

    public function getRequestData(): AbstractRequestData
    {
        return $this->requestData;
    }
}
