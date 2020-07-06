<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Subscriber;

use Exception;
use Monolog\Logger;
use Ratepay\RatepayPayments\Components\OrderManagement\Util\LineItemUtil;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\AddCreditData;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\ResponseEvent;
use Ratepay\RatepayPayments\Components\Logging\Service\HistoryLogger;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentCancelService;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentCreditService;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentDeliverService;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentReturnService;
use Ratepay\RatepayPayments\Util\CriteriaHelper;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Order\RecalculationService;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentChangeSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $productRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $lineItemsRepository;
    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var HistoryLogger
     */
    private $historyLogger;
    /**
     * @var RecalculationService
     */
    private $recalculationService;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityRepositoryInterface $productRepository,
        EntityRepositoryInterface $orderRepository,
        EntityRepositoryInterface $lineItemsRepository,
        RecalculationService $recalculationService,
        Logger $logger,
        HistoryLogger $historyLogger
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->lineItemsRepository = $lineItemsRepository;
        $this->recalculationService = $recalculationService;
        $this->logger = $logger;
        $this->historyLogger = $historyLogger;
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

        $data = [];
        /** @var OrderLineItemEntity $item */
        foreach ($requestData->getItems() as $id => $qty) {
            if($id === 'shipping') {
                $customFields = $requestData->getOrder()->getCustomFields();
                $ratepayCustomFields = $customFields['ratepay']['shipping'] ?? LineItemUtil::getEmptyCustomFields();
                $ratepayCustomFields = $this->updateCustomField($requestData, $ratepayCustomFields, $qty);
                $customFields['ratepay']['shipping'] = $ratepayCustomFields;

                $this->orderRepository->update([
                    [
                        'id' => $requestData->getOrder()->getId(),
                        'customFields' => $customFields
                    ]
                ], $event->getContext());

                $productName = $id;
                $productNumber = $id;
            } else {
                /** @var OrderLineItemEntity $item */
                $item = $this->lineItemsRepository->search(new Criteria([$id]), $event->getContext())->first();

                $customFields = $item->getCustomFields();
                $ratepayCustomFields = $customFields['ratepay'] ?? LineItemUtil::getEmptyCustomFields();
                $customFields['ratepay'] = $this->updateCustomField($requestData, $ratepayCustomFields, $qty);
                $data[] = [
                    'id' => $item->getId(),
                    'customFields' => $customFields
                ];

                $productName = $item->getLabel();
                $productNumber = $item->getPayload()['productNumber'] ?? $id;
            }

            $this->historyLogger->logHistory(
                $event->getContext(),
                $requestData->getOrder()->getId(),
                $requestData->getOperation(),
                $productName,
                $productNumber,
                $qty
            );
        }
        if ($data) {
            $this->lineItemsRepository->update($data, $event->getContext());
        }

        if ($requestData->isUpdateStock()) {
            $this->updateProductStocks($event->getContext(), $requestData);
        }
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
            $newItems[$item->getId()] = $item->getPriceDefinition()->getQuantity();
        }
        // recalculate the whole order. (without this, shipping costs will added to the order if there is a shipping free position - RATESWSX-71)
        $this->recalculationService->recalculateOrder($requestData->getOrder()->getId(), $versionContext);
        // merge the order with the SYSTEM_SCOPE, cause the recalculateOrder locked the order with this scope.
        $event->getContext()->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($versionId): void {
            $this->orderRepository->merge($versionId, $context);
        });

        $reloadedOrder = $this->orderRepository->search(
            CriteriaHelper::getCriteriaForOrder($requestData->getOrder()->getId()),
            $event->getContext()
        )->first();

        // trigger deliver event
        $this->eventDispatcher->dispatch(new ResponseEvent(
            $event->getContext(),
            $event->getRequestBuilder(),
            new OrderOperationData($reloadedOrder, OrderOperationData::OPERATION_ADD, $newItems, false)
        ), PaymentDeliverService::EVENT_SUCCESSFUL);
    }

    protected function updateCustomField(OrderOperationData $requestData, ?array $customFields, $qty)
    {
        switch ($requestData->getOperation()) {
            case OrderOperationData::OPERATION_ADD:
            case OrderOperationData::OPERATION_DELIVER:
                $customFields['delivered'] += $qty;
                break;
            case OrderOperationData::OPERATION_CANCEL:
                $customFields['canceled'] += $qty;
                break;
            case OrderOperationData::OPERATION_RETURN:
                $customFields['returned'] += $qty;
                break;
        }
        return $customFields;
    }

    protected function updateProductStocks(Context $context, OrderOperationData $requestData): void
    {
        $items = $requestData->getItems();
        unset($items['shipping']); // "shipping" is not a valid uuid - maybe an error will throw (in the future)

        $lineItems = $requestData->getOrder()->getLineItems()->getList(array_keys($items));
        $data = [];
        /** @var OrderLineItemEntity $item */
        foreach ($lineItems as $item) {
            if($item->getProduct()) {
                // verify if the product still exists
                $data[] = [
                    'id' => $item->getProduct()->getId(),
                    'stock' => $item->getProduct()->getStock() + $requestData->getItems()[$item->getId()],
                ];
            }
        }
        if(count($data) === 0) {
            // nothing to do
            return;
        }
        try {
            $this->productRepository->update($data, $context);
        } catch (Exception $e) {
            $this->logger->addError('Error during the updating of the stock', [
                'message' => $e->getMessage(),
                'orderId' => $requestData->getOrder()->getId(),
                'orderNumber' => $requestData->getOrder()->getOrderNumber(),
                'items' => $requestData->getItems(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

}
