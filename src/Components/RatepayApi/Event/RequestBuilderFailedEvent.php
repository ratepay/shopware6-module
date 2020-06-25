<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Event;

use Exception;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\IRequestData;
use Symfony\Contracts\EventDispatcher\Event;

class RequestBuilderFailedEvent extends Event
{
    /**
     * @var Exception
     */
    protected $exception;
    /**
     * @var IRequestData
     */
    private $requestData;

    public function __construct(Exception $exception, IRequestData $requestData)
    {
        $this->exception = $exception;
        $this->requestData = $requestData;
    }

    public function getException(): Exception
    {
        return $this->exception;
    }

    public function getRequestData(): IRequestData
    {
        return $this->requestData;
    }
}
