<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Subscriber;

use Ratepay\RpayPayments\Components\RatepayApi\Event\InitEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\TransactionIdService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentRequestSubscriber implements EventSubscriberInterface
{
    /**
     * @var TransactionIdService
     */
    private $transactionIdService;

    public function __construct(TransactionIdService $transactionIdService)
    {
        $this->transactionIdService = $transactionIdService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentRequestService::EVENT_INIT_REQUEST => ['addTransactionId', 10],
            PaymentRequestService::EVENT_SUCCESSFUL => ['deleteTransactionId', 10],
        ];
    }

    public function addTransactionId(InitEvent $event): void
    {
        $requestData = $event->getRequestData();
        $ratepayTransactionId = $this->transactionIdService->getTransactionId($requestData->getSalesChannelContext());
        $requestData->setRatepayTransactionId($ratepayTransactionId);
    }

    public function deleteTransactionId(RequestDoneEvent $doneEvent): void
    {
    }
}
