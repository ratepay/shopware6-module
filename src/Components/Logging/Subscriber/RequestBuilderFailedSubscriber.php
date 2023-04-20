<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Subscriber;

use Monolog\Logger;
use Ratepay\RpayPayments\Components\RatepayApi\Event\RequestBuilderFailedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestBuilderFailedSubscriber implements EventSubscriberInterface
{
    protected Logger $logger;

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
        // $requestData = $event->getRequestData();
        $exception = $event->getException();
        $this->logger->error('RequestBuilder failed', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
