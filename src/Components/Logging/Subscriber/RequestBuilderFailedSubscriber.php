<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Logging\Subscriber;

use Monolog\Logger;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\RequestBuilderFailedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestBuilderFailedSubscriber implements EventSubscriberInterface
{
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(Logger $fileLogger)
    {
        $this->logger = $fileLogger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestBuilderFailedEvent::class => 'onRequestBuilderFailed',
        ];
    }

    public function onRequestBuilderFailed(RequestBuilderFailedEvent $event): void
    {
        //$requestData = $event->getRequestData();
        $exception = $event->getException();
        $this->logger->error('RequestBuilder failed', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
