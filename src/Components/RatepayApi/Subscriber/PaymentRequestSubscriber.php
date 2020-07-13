<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Subscriber;


use RatePAY\Model\Response\PaymentRequest;
use Ratepay\RatepayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RatepayPayments\Components\Checkout\Model\RatepayOrderLineItemDataEntity;
use Ratepay\RatepayPayments\Components\Checkout\Model\RatepayPositionEntity;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\ResponseEvent;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentRequestSubscriber implements EventSubscriberInterface
{

    /**
     * @var EntityRepositoryInterface
     */
    private $ratepayOrderExtensionRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $ratepayOrderLineItemExtensionRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $ratepayPositionRepository;

    public function __construct(
        EntityRepositoryInterface $ratepayOrderExtensionRepository,
        EntityRepositoryInterface $ratepayOrderLineItemExtensionRepository,
        EntityRepositoryInterface $ratepayPositionRepository
    )
    {
        $this->ratepayOrderExtensionRepository = $ratepayOrderExtensionRepository;
        $this->ratepayOrderLineItemExtensionRepository = $ratepayOrderLineItemExtensionRepository;
        $this->ratepayPositionRepository = $ratepayPositionRepository;
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

        $orderItems = $requestData->getOrder()->getLineItems();

        $lineItemsData = [];
        $positions = [];
        $shippingPositionId = null;
        foreach ($requestData->getItems() as $id => $item) {
            $positionId = Uuid::randomHex();

            $positions[] = [
                RatepayPositionEntity::FIELD_ID => $positionId
            ];

            if ($id === 'shipping') {
                $shippingPositionId = $positionId;
            } else {
                $lineItem = $orderItems->get($id);

                $lineItemsData[] = [
                    RatepayOrderLineItemDataEntity::FIELD_POSITION_ID => $positionId,
                    RatepayOrderLineItemDataEntity::FIELD_ORDER_LINE_ITEM_ID => $lineItem->getId(),
                    RatepayOrderLineItemDataEntity::FIELD_ORDER_LINE_ITEM_VERSION_ID => $lineItem->getVersionId()
                ];
            }
        }

        $this->ratepayPositionRepository->create($positions, $requestData->getSalesChannelContext()->getContext());
        $this->ratepayOrderLineItemExtensionRepository->create($lineItemsData, $requestData->getSalesChannelContext()->getContext());

        $requestXml = $requestEvent->getRequestBuilder()->getRequestXmlElement();

        $this->ratepayOrderExtensionRepository->create([
            [
                RatepayOrderDataEntity::FIELD_ORDER_ID => $requestData->getOrder()->getId(),
                RatepayOrderDataEntity::FIELD_ORDER_VERSION_ID => $requestData->getOrder()->getVersionId(),
                RatepayOrderDataEntity::FIELD_PROFILE_ID => (string)$requestXml->head->credential->{"profile-id"},
                RatepayOrderDataEntity::FIELD_TRANSACTION_ID => $responseModel->getTransactionId(),
                RatepayOrderDataEntity::FIELD_SHIPPING_POSITION_ID => $shippingPositionId
            ]
        ], $requestData->getSalesChannelContext()->getContext());
    }
}
