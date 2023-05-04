<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Subscriber;

use Psr\Log\LoggerInterface;
use Ratepay\RpayPayments\Components\RatepayApi\Event\RequestBuilderFailedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestBuilderFailedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $fileLogger
    ) {
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
        $this->fileLogger->error('RequestBuilder failed', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
