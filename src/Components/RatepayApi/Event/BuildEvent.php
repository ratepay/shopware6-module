<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Event;

use RatePAY\Model\Request\SubModel\AbstractModel;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @template T of AbstractModel
 */
class BuildEvent extends Event
{
    /**
     * @param T|null $buildData
     */
    public function __construct(
        private readonly AbstractRequestData $requestData,
        private readonly ?object $buildData = null
    ) {
    }

    public function getRequestData(): AbstractRequestData
    {
        return $this->requestData;
    }

    /**
     * @return T|null
     */
    public function getBuildData(): ?object
    {
        return $this->buildData;
    }
}
