<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
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

    public function __construct(AbstractRequestData $requestData, $buildData)
    {
        $this->requestData = $requestData;
        $this->buildData = $buildData;
    }

    public function getRequestData(): AbstractRequestData
    {
        return $this->requestData;
    }

    /**
     * @return mixed
     */
    public function getBuildData()
    {
        return $this->buildData;
    }
}
