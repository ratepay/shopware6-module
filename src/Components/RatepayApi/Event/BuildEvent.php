<?php

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
    /**
     * @var AbstractRequestData
     */
    private $requestData;

    private $buildData;

    public function __construct(AbstractRequestData $requestData, $buildData = null)
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
