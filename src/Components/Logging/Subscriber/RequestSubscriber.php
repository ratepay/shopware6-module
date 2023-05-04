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
use RatePAY\Model\Response\PaymentRequest;
use Ratepay\RpayPayments\Components\Logging\Service\ApiLogger;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentFailedEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;

class RequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ApiLogger $apiLogger,
        private readonly LoggerInterface $fileLogger
    ) {
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
        if ($exception instanceof Throwable) {
            $exception = $exception->getPrevious() ?? $exception;
            $message = $exception->getMessage();
        } elseif ($event->getResponse() instanceof PaymentRequest) {
            $message = (string) $event->getResponse()->getReasonMessage();
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
