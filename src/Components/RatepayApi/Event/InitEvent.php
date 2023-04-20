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

class InitEvent extends Event
{
    private AbstractRequestData $requestData;

    public function __construct(AbstractRequestData $requestData)
    {
        $this->requestData = $requestData;
    }

    public function getRequestData(): AbstractRequestData
    {
        return $this->requestData;
    }
}
