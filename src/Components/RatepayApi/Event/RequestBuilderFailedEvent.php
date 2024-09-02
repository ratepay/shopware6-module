<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Event;

use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Symfony\Contracts\EventDispatcher\Event;
use Throwable;

class RequestBuilderFailedEvent extends Event
{
    public function __construct(
        private readonly Throwable $exception,
        private readonly AbstractRequestData $requestData
    ) {
    }

    public function getThrowable(): Throwable
    {
        return $this->exception;
    }

    /**
     * @deprecated use getThrowable
     */
    public function getException(): Throwable
    {
        return $this->getThrowable();
    }

    public function getRequestData(): AbstractRequestData
    {
        return $this->requestData;
    }
}
