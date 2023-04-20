<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Subscriber;

use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Event\InitEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Model\TransactionIdEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\TransactionIdService;
use Ratepay\RpayPayments\Exception\RatepayException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentRequestSubscriber implements EventSubscriberInterface
{
    private TransactionIdService $transactionIdService;

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
        /** @var PaymentRequestData $requestData */
        $requestData = $event->getRequestData();

        $ratepayTransactionId = $this->transactionIdService->getStoredTransactionId(
            $requestData->getSalesChannelContext(),
            TransactionIdService::PREFIX_ORDER . $requestData->getOrder()->getId()
        );

        if (!$ratepayTransactionId instanceof TransactionIdEntity) {
            throw new RatepayException('Stored transaction id was not found');
        }

        $requestData->setRatepayTransactionId($ratepayTransactionId->getTransactionId());
    }

    public function deleteTransactionId(RequestDoneEvent $doneEvent): void
    {
    }
}
