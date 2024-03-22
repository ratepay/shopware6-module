<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Subscriber;

use Exception;
use Psr\Log\LoggerInterface;
use Ratepay\RpayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RpayPayments\Components\Logging\Service\HistoryLogger;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AddCreditData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Event\ResponseEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentCancelService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentCreditService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentDeliverService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentReturnService;
use Ratepay\RpayPayments\Core\Entity\Extension\OrderExtension;
use Ratepay\RpayPayments\Core\Entity\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Core\Entity\RatepayOrderLineItemDataEntity;
use Ratepay\RpayPayments\Core\Entity\RatepayPositionEntity;
use Ratepay\RpayPayments\Core\Event\OrderItemOperationDoneEvent;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Order\RecalculationService;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentChangeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityRepository $productRepository,
        private readonly EntityRepository $orderRepository,
        private readonly EntityRepository $ratepayPositionRepository,
        private readonly ExtensionService $extensionService,
        private readonly RecalculationService $recalculationService,
        private readonly LoggerInterface $logger,
        private readonly HistoryLogger $historyLogger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentCancelService::EVENT_SUCCESSFUL => 'onSuccess',
            PaymentDeliverService::EVENT_SUCCESSFUL => 'onSuccess',
            PaymentReturnService::EVENT_SUCCESSFUL => 'onSuccess',
            PaymentCreditService::EVENT_SUCCESSFUL => 'onSuccessAddCredit',
        ];
    }

    public function onSuccess(ResponseEvent $event): void
    {
        /** @var OrderOperationData $requestData */
        $requestData = $event->getRequestData();

        $positionUpdates = [];
        foreach ($requestData->getItems() as $id => $qty) {
            if ($id === OrderOperationData::ITEM_ID_SHIPPING) {
                /** @var RatepayOrderDataEntity $ratepayData */
                $ratepayData = $requestData->getOrder()->getExtension(OrderExtension::EXTENSION_NAME);
                $position = $ratepayData->getShippingPosition();

                $productName = $id;
                $productNumber = $id;
            } else {
                /** @var OrderLineItemEntity $lineItem */
                $lineItem = $requestData->getOrder()->getLineItems()->get($id);

                $ratepayData = $lineItem->getExtension(OrderExtension::EXTENSION_NAME);
                if (!$ratepayData instanceof RatepayOrderLineItemDataEntity) {
                    // will occur if the item has been just added to the order
                    $ratepayData = $this->extensionService->createLineItemExtensionEntities([$lineItem], $event->getContext())->first();
                }

                $position = $ratepayData->getPosition();

                $productName = $lineItem->getLabel();
                $productNumber = $lineItem->getPayload()['productNumber'] ?? $id;
            }

            $updateData = $this->getPositionUpdates($requestData, $position, (int) $qty);
            $updateData[RatepayPositionEntity::FIELD_ID] = $position->getId();
            $positionUpdates[] = $updateData;

            // todo trigger event
            $this->historyLogger->logHistory(
                $event->getContext(),
                $requestData->getOrder()->getId(),
                $requestData->getOperation(),
                $productName,
                $productNumber,
                $qty
            );
        }

        if ($positionUpdates !== []) {
            $this->ratepayPositionRepository->upsert($positionUpdates, $event->getContext());
        }

        if ($requestData->isUpdateStock()) {
            $this->updateProductStocks($event->getContext(), $requestData);
        }

        /** @var OrderEntity $order */
        $order = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($requestData->getOrder()->getId()), $event->getContext())->first();

        $this->eventDispatcher->dispatch(new OrderItemOperationDoneEvent(
            $order,
            $requestData,
            $event->getContext()
        ));
    }

    public function onSuccessAddCredit(ResponseEvent $event): void
    {
        /** @var AddCreditData $requestData */
        $requestData = $event->getRequestData();

        $versionId = $this->orderRepository->createVersion($requestData->getOrder()->getId(), $event->getContext());
        $versionContext = $event->getContext()->createWithVersionId($versionId);

        $newItems = [];
        /** @var LineItem $item */
        foreach ($requestData->getItems() as $item) {
            $this->recalculationService->addCustomLineItem($requestData->getOrder()->getId(), $item, $versionContext);
            $newItems[$item->getId()] = $item->getPriceDefinition() instanceof QuantityPriceDefinition ? $item->getPriceDefinition()->getQuantity() : 1;
        }

        // recalculate the whole order. (without this, shipping costs will added to the order if there is a shipping free position - RATESWSX-71)
        $this->recalculationService->recalculateOrder($requestData->getOrder()->getId(), $versionContext);
        // merge the order with the SYSTEM_SCOPE, cause the recalculateOrder locked the order with this scope.
        $event->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($versionId): void {
            $this->orderRepository->merge($versionId, $context);
        });

        /** @var OrderEntity $reloadedOrder */
        $reloadedOrder = $this->orderRepository->search(
            CriteriaHelper::getCriteriaForOrder($requestData->getOrder()->getId()),
            $event->getContext()
        )->first();

        // trigger deliver event
        $this->eventDispatcher->dispatch(new ResponseEvent(
            $event->getContext(),
            $event->getRequestBuilder(),
            new OrderOperationData($event->getContext(), $reloadedOrder, OrderOperationData::OPERATION_ADD, $newItems, false)
        ), PaymentDeliverService::EVENT_SUCCESSFUL);

        $this->eventDispatcher->dispatch(new OrderItemOperationDoneEvent(
            $reloadedOrder,
            $requestData,
            $event->getContext()
        ));
    }

    /**
     * @return array<string, int>
     */
    protected function getPositionUpdates(OrderOperationData $requestData, RatepayPositionEntity $position, int $qty): array
    {
        $updates = [];
        switch ($requestData->getOperation()) {
            case OrderOperationData::OPERATION_ADD:
            case OrderOperationData::OPERATION_DELIVER:
                $updates[RatepayPositionEntity::FIELD_DELIVERED] = $position->getDelivered() + $qty;
                break;
            case OrderOperationData::OPERATION_CANCEL:
                $updates[RatepayPositionEntity::FIELD_CANCELED] = $position->getCanceled() + $qty;
                break;
            case OrderOperationData::OPERATION_RETURN:
                $updates[RatepayPositionEntity::FIELD_RETURNED] = $position->getReturned() + $qty;
                break;
        }

        return $updates;
    }

    protected function updateProductStocks(Context $context, OrderOperationData $requestData): void
    {
        $items = $requestData->getItems();
        unset($items[OrderOperationData::ITEM_ID_SHIPPING]); // "shipping" is not a valid uuid - maybe an error will throw (in the future)

        $lineItems = $requestData->getOrder()->getLineItems()->getList(array_keys($items));
        $data = [];
        /** @var OrderLineItemEntity $item */
        foreach ($lineItems as $item) {
            if ($item->getProduct() instanceof ProductEntity) {
                // verify if the product still exists
                $data[] = [
                    'id' => $item->getProduct()->getId(),
                    'stock' => $item->getProduct()->getStock() + $requestData->getItems()[$item->getId()],
                ];
            }
        }

        if ($data === []) {
            // nothing to do
            return;
        }

        try {
            $this->productRepository->update($data, $context);
        } catch (Exception $exception) {
            // todo trigger event
            $this->logger->error('Error during the updating of the stock', [
                'message' => $exception->getMessage(),
                'orderId' => $requestData->getOrder()->getId(),
                'orderNumber' => $requestData->getOrder()->getOrderNumber(),
                'items' => $requestData->getItems(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }
}
