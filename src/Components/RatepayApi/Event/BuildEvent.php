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

class BuildEvent extends Event
{
    private AbstractRequestData $requestData;

    private ?object $buildData;

    public function __construct(AbstractRequestData $requestData, ?object $buildData = null)
    {
        $this->requestData = $requestData;
        $this->buildData = $buildData;
    }

    public function getRequestData(): AbstractRequestData
    {
        return $this->requestData;
    }

    public function getBuildData(): ?object
    {
        return $this->buildData;
    }
}
