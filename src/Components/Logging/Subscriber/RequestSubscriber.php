<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Subscriber;

use Monolog\Logger;
use Ratepay\RpayPayments\Components\Logging\Service\ApiLogger;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentFailedEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestSubscriber implements EventSubscriberInterface
{
    protected ApiLogger $apiLogger;

    private Logger $fileLogger;

    public function __construct(ApiLogger $apiLogger, Logger $fileLogger)
    {
        $this->apiLogger = $apiLogger;
        $this->fileLogger = $fileLogger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestDoneEvent::class => 'onRequestDone',
            PaymentFailedEvent::class => 'onPaymentFailed',
        ];
    }

    public function onPaymentFailed(PaymentFailedEvent $event): void
    {
        $exception = $event->getException();
        if ($exception !== null) {
            $exception = $exception->getPrevious() ?? $exception;
            $message = $exception->getMessage();
        } elseif ($event->getResponse() !== null) {
            $message = $event->getResponse()->getReasonMessage();
        }

        $this->fileLogger->error($message ?? 'Unknown error', [
            'order_id' => $event->getOrder()->getId(),
            'order_number' => $event->getOrder()->getOrderNumber(),
            'request_bag' => $event->getRequestDataBag(),
        ]);
    }

    public function onRequestDone(RequestDoneEvent $event): void
    {
        $this->apiLogger->logRequest($event);
    }
}
