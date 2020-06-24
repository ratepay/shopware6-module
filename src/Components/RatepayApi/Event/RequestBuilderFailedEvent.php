<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Event;

use Exception;
use Symfony\Contracts\EventDispatcher\Event;

class RequestBuilderFailedEvent extends Event
{
    /**
     * @var Exception
     */
    protected $exception;

    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }

    public function getException(): Exception
    {
        return $this->exception;
    }
}
