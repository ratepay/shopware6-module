<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Subscriber;

use RatePAY\Model\Response\PaymentRequest;
use Ratepay\RpayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Event\ResponseEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\TransactionIdService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentRequestSubscriber implements EventSubscriberInterface
{
    /**
     * @var ExtensionService
     */
    private $extensionService;

    /**
     * @var TransactionIdService
     */
    private $transactionIdService;

    public function __construct(
        TransactionIdService $transactionIdService,
        ExtensionService $extensionService
    ) {
        $this->transactionIdService = $transactionIdService;
        $this->extensionService = $extensionService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentRequestService::EVENT_SUCCESSFUL => 'onSuccess',
            PaymentRequestService::EVENT_FAILED => 'onFailure',
        ];
    }

    public function onFailure(ResponseEvent $requestEvent)
    {
        /** @var PaymentRequestData $requestData */
        $requestData = $requestEvent->getRequestData();

        $this->transactionIdService->deleteTransactionId($requestData->getRatepayTransactionId(), $requestData->getContext());

        /** @var PaymentRequest $responseModel */
        $responseModel = $requestEvent->getRequestBuilder()->getResponse();

        $this->extensionService->createOrderExtensionEntity(
            $requestData->getOrder(),
            $responseModel->getTransactionId(),
            $responseModel->getDescriptor(),
            $requestData->getProfileConfig()->getProfileId(),
            false,
            $requestData->getContext()
        );
    }

    public function onSuccess(ResponseEvent $requestEvent)
    {
        /** @var PaymentRequestData $requestData */
        $requestData = $requestEvent->getRequestData();

        $this->transactionIdService->deleteTransactionId($requestData->getRatepayTransactionId(), $requestData->getContext());

        /** @var PaymentRequest $responseModel */
        $responseModel = $requestEvent->getRequestBuilder()->getResponse();

        $orderItems = $requestData->getOrder()->getLineItems();

        $lineItems = [];
        foreach ($requestData->getItems() as $id => $item) {
            if ($id !== 'shipping') {
                // shipping will written into the order-extension
                $lineItems[] = $orderItems->get($id);
            }
        }
        $this->extensionService->createLineItemExtensionEntities($lineItems, $requestData->getContext());

        $this->extensionService->createOrderExtensionEntity(
            $requestData->getOrder(),
            $responseModel->getTransactionId(),
            $responseModel->getDescriptor(),
            $requestData->getProfileConfig()->getProfileId(),
            true,
            $requestData->getContext()
        );
    }
}
