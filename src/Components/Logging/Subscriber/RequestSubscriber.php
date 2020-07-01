<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Logging\Subscriber;

use Ratepay\RatepayPayments\Components\Logging\Service\ApiLogger;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestSubscriber implements EventSubscriberInterface
{
    /**
     * @var ApiLogger
     */
    protected $apiLogger;

    public function __construct(ApiLogger $apiLogger)
    {
        $this->apiLogger = $apiLogger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestDoneEvent::class => 'onRequestDone',
        ];
    }

    public function onRequestDone(RequestDoneEvent $event): void
    {
        $this->apiLogger->logRequest($event);
    }
}
