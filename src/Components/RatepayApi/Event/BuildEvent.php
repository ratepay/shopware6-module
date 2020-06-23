<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Event;


use Ratepay\RatepayPayments\Components\RatepayApi\Dto\IRequestData;
use Symfony\Contracts\EventDispatcher\Event;

class BuildEvent extends Event
{

    /**
     * @var IRequestData
     */
    private $requestData;
    private $buildData;

    public function __construct(IRequestData $requestData, $buildData)
    {
        $this->requestData = $requestData;
        $this->buildData = $buildData;
    }

    /**
     * @return IRequestData
     */
    public function getRequestData(): IRequestData
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