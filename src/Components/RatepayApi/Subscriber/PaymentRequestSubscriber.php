<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Subscriber;

use RatePAY\Model\Response\PaymentRequest;
use Ratepay\RpayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Event\ResponseEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ExtensionService $extensionService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentRequestService::EVENT_SUCCESSFUL => 'onSuccess',
            PaymentRequestService::EVENT_FAILED => 'onFailure',
        ];
    }

    public function onFailure(ResponseEvent $requestEvent): void
    {
        /** @var PaymentRequestData $requestData */
        $requestData = $requestEvent->getRequestData();

        /** @var PaymentRequest $responseModel */
        $responseModel = $requestEvent->getRequestBuilder()->getResponse();

        $this->extensionService->createOrderExtensionEntity(
            $requestData,
            $responseModel->getTransactionId(),
            $responseModel->getDescriptor(),
            false
        );
    }

    public function onSuccess(ResponseEvent $requestEvent): void
    {
        /** @var PaymentRequestData $requestData */
        $requestData = $requestEvent->getRequestData();

        /** @var PaymentRequest $responseModel */
        $responseModel = $requestEvent->getRequestBuilder()->getResponse();

        $orderItems = $requestData->getOrder()->getLineItems();

        $lineItems = [];
        foreach (array_keys($requestData->getItems()) as $id) {
            if ($id !== OrderOperationData::ITEM_ID_SHIPPING) {
                // shipping will be written into the order-extension
                $lineItems[] = $orderItems->get($id);
            }
        }

        $this->extensionService->createLineItemExtensionEntities($lineItems, $requestData->getContext());

        $this->extensionService->createOrderExtensionEntity(
            $requestData,
            $responseModel->getTransactionId(),
            $responseModel->getDescriptor(),
            true
        );
    }
}
