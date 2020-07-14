<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Subscriber;


use RatePAY\Model\Response\PaymentRequest;
use Ratepay\RatepayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\ResponseEvent;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentRequestSubscriber implements EventSubscriberInterface
{

    /**
     * @var ExtensionService
     */
    private $extensionService;

    public function __construct(
        ExtensionService $extensionService
    )
    {
        $this->extensionService = $extensionService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentRequestService::EVENT_SUCCESSFUL => 'onSuccess'
        ];
    }

    public function onSuccess(ResponseEvent $requestEvent)
    {
        /** @var PaymentRequestData $requestData */
        $requestData = $requestEvent->getRequestData();

        /** @var PaymentRequest $responseModel */
        $responseModel = $requestEvent->getRequestBuilder()->getResponse();
        $requestXml = $requestEvent->getRequestBuilder()->getRequestXmlElement();

        $orderItems = $requestData->getOrder()->getLineItems();

        $lineItems = [];
        foreach ($requestData->getItems() as $id => $item) {
            if ($id !== 'shipping') {
                // shipping will written into the order-extension
                $lineItems[] = $orderItems->get($id);
            }
        }
        $this->extensionService->createLineItemExtensions(
            $lineItems,
            $requestData->getSalesChannelContext()->getContext()
        );


        $this->extensionService->createOrderExtension(
            $requestData->getOrder(),
            $responseModel->getTransactionId(),
            (string)$requestXml->head->credential->{"profile-id"},
            $requestData->getSalesChannelContext()->getContext()
        );

    }
}
