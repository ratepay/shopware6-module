<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Logging\Subscriber;

use Monolog\Logger;
use Ratepay\RatepayPayments\Components\Logging\Service\ApiLogger;
use Ratepay\RatepayPayments\Components\PaymentHandler\Event\PaymentFailedEvent;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestSubscriber implements EventSubscriberInterface
{
    /**
     * @var ApiLogger
     */
    protected $apiLogger;
    /**
     * @var Logger
     */
    private $fileLogger;

    public function __construct(ApiLogger $apiLogger, Logger $fileLogger)
    {
        $this->apiLogger = $apiLogger;
        $this->fileLogger = $fileLogger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestDoneEvent::class => 'onRequestDone',
            PaymentFailedEvent::class => 'onPaymentFailed'
        ];
    }

    public function onPaymentFailed(PaymentFailedEvent $event)
    {
        if($event->getResponse()) {
            $message = $event->getResponse()->getReasonMessage();
        } else if($event->getException()) {
            $message = $event->getException()->getMessage();
        }
        $this->fileLogger->addError($message ?? 'Unknown error', [
            'order_id' => $event->getOrder()->getId(),
            'order_number' => $event->getOrder()->getOrderNumber(),
            'request_bag' => $event->getRequestDataBag()

        ]);
    }

    public function onRequestDone(RequestDoneEvent $event): void
    {
        $this->apiLogger->logRequest($event);
    }
}
