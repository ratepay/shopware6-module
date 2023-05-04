<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
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
    public function __construct(
        private readonly Exception $exception,
        private readonly AbstractRequestData $requestData
    ) {
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
