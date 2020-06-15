<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Subscriber;


use RatePAY\Model\Response\PaymentRequest;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\ResponseEvent;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\Request\PaymentRequestService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentRequestSubscriber implements EventSubscriberInterface
{

    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $lineItemsRepository;

    public function __construct(
        EntityRepositoryInterface $orderRepository,
        EntityRepositoryInterface $lineItemsRepository
    )
    {
        $this->orderRepository = $orderRepository;
        $this->lineItemsRepository = $lineItemsRepository;
    }

    public static function getSubscribedEvents()
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

        $customFields = $requestData->getOrder()->getCustomFields() ?? [];
        $customFields['ratepay']['transaction_id'] = $responseModel->getTransactionId();
        $customFields['ratepay']['shipping']['delivered'] = 0;
        $customFields['ratepay']['shipping']['returned'] = 0;
        $customFields['ratepay']['shipping']['canceled'] = 0;

        $this->orderRepository->upsert([
            [
                'id' => $requestData->getOrder()->getId(),
                'customFields' => $customFields
            ]
        ], $requestEvent->getContext());

        $lineItems = [];
        foreach ($requestData->getOrder()->getLineItems() as $item) {
            $itemCustomFields = $item->getCustomFields() ?? [];
            $itemCustomFields['ratepay']['delivered'] = 0;
            $itemCustomFields['ratepay']['returned'] = 0;
            $itemCustomFields['ratepay']['canceled'] = 0;

            $lineItems[] = [
                'id' => $item->getId(),
                'customFields' => $itemCustomFields
            ];
        }
        $this->lineItemsRepository->upsert($lineItems, $requestEvent->getContext());
    }
}
