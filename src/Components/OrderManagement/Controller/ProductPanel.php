<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\OrderManagement\Controller;

use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderLineItemExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderLineItemDataEntity;
use Ratepay\RpayPayments\Components\OrderManagement\Service\LineItemFactory;
use Ratepay\RpayPayments\Components\OrderManagement\Util\LineItemUtil;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AddCreditData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\AbstractModifyRequest;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentCancelService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentCreditService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentDeliverService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentReturnService;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/ratepay/order-management", defaults={"_routeScope"={"administration"}})
 */
class ProductPanel extends AbstractController
{
    private EntityRepository $orderRepository;

    private PaymentCreditService $creditService;

    /**
     * @var AbstractModifyRequest[]
     */
    private array $requestServicesByOperation = [];

    private OrderConverter $orderConverter;

    private LineItemFactory $lineItemFactory;

    public function __construct(
        OrderConverter $orderConverter,
        EntityRepository $orderRepository,
        PaymentDeliverService $paymentDeliverService,
        PaymentReturnService $paymentReturnService,
        PaymentCancelService $paymentCancelService,
        PaymentCreditService $creditService,
        LineItemFactory $lineItemFactory
    ) {
        $this->orderConverter = $orderConverter;
        $this->orderRepository = $orderRepository;
        $this->creditService = $creditService;

        $this->requestServicesByOperation = [
            OrderOperationData::OPERATION_DELIVER => $paymentDeliverService,
            OrderOperationData::OPERATION_CANCEL => $paymentCancelService,
            OrderOperationData::OPERATION_RETURN => $paymentReturnService,
        ];
        $this->lineItemFactory = $lineItemFactory;
    }

    /**
     * @Route("/load/{orderId}", name="ratepay.order_management.product_panel.load", methods={"GET"})
     */
    public function load(string $orderId, Context $context): JsonResponse
    {
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('lineItems');

        /** @var OrderEntity $order */
        $order = $this->orderRepository->search($criteria, $context)->first();

        if ($order) {
            $items = [];
            foreach ($order->getLineItems() as $lineItem) {
                /** @var RatepayOrderLineItemDataEntity $extension */
                if ($extension = $lineItem->getExtension(OrderLineItemExtension::EXTENSION_NAME)) {
                    $items[$lineItem->getId()] = [
                        'id' => $lineItem->getId(),
                        'name' => $lineItem->getLabel(),
                        'ordered' => $lineItem->getQuantity(),
                        'unitPrice' => $lineItem->getUnitPrice(),
                        'totalPrice' => $lineItem->getTotalPrice(),
                        'position' => LineItemUtil::addMaxActionValues(
                            $extension->getPosition(),
                            $lineItem->getQuantity()
                        ),
                    ];
                }
            }

            /** @var $orderExtension RatepayOrderDataEntity */
            if (($orderExtension = $order->getExtension(OrderExtension::EXTENSION_NAME)) &&
                $orderExtension->getShippingPosition()) {
                $items[OrderOperationData::ITEM_ID_SHIPPING] = [
                    'id' => OrderOperationData::ITEM_ID_SHIPPING,
                    'name' => 'shipping',
                    'ordered' => 1,
                    'unitPrice' => $order->getShippingTotal(),
                    'totalPrice' => $order->getShippingTotal(),
                    'position' => LineItemUtil::addMaxActionValues($orderExtension->getShippingPosition(), 1),
                ];
            }

            return $this->json([
                'success' => true,
                'data' => $items,
            ], 200);
        }

        return $this->json([
            'success' => false,
            'message' => 'Order not found',
        ], 400);
    }

    /**
     * @Route("/deliver/{orderId}", name="ratepay.order_management.product_panel.deliver", methods={"POST"})
     */
    public function deliver(string $orderId, Request $request, Context $context): JsonResponse
    {
        return $this->processModify($request, $context, OrderOperationData::OPERATION_DELIVER, $orderId);
    }

    protected function processModify(Request $request, Context $context, string $operation, string $orderId): JsonResponse
    {
        $order = $this->fetchOrder($context, $orderId);

        if ($order !== null) {
            $items = [];
            foreach ($request->request->get('items') ?? [] as $data) {
                $items[$data['id']] = (int) $data['quantity'];
            }

            $items = array_filter($items, static fn($quantity): bool => $quantity > 0);

            if ($items === []) {
                return $this->json([
                    'success' => false,
                    'message' => 'Please provide at least on item', // todo translation - should we translate it?
                ], 200); // todo is this status OK ?
            }

            $response = $this->requestServicesByOperation[$operation]->doRequest(
                new OrderOperationData($context, $order, $operation, $items, $request->request->get('updateStock') == true)
            );

            return $this->json([
                'success' => $response->getResponse()->isSuccessful(),
                'message' => $response->getResponse()->getReasonMessage(),
            ], 200);
        }

        return $this->json([
            'success' => false,
            'message' => 'Order not found',
        ], 400);
    }

    /**
     * @Route("/cancel/{orderId}", name="ratepay.order_management.product_panel.cancel", methods={"POST"})
     */
    public function cancel(string $orderId, Request $request, Context $context): JsonResponse
    {
        return $this->processModify($request, $context, OrderOperationData::OPERATION_CANCEL, $orderId);
    }

    /**
     * @Route("/return/{orderId}", name="ratepay.order_management.product_panel.return", methods={"POST"})
     */
    public function return(string $orderId, Request $request, Context $context): JsonResponse
    {
        return $this->processModify($request, $context, OrderOperationData::OPERATION_RETURN, $orderId);
    }

    /**
     * @Route("/addItem/{orderId}", name="ratepay.order_management.product_panel.add_item", methods={"POST"})
     */
    public function addItem(string $orderId, Request $request, Context $context): JsonResponse
    {
        $name = $request->request->get('name');
        $grossAmount = $request->request->get('grossAmount');
        $taxRuleId = $request->request->get('taxId');

        $order = $this->fetchOrder($context, $orderId);

        if ($order === null) {
            return $this->json([
                'success' => false,
                'message' => 'Order was not found',
            ], 200);
        }

        $lineItem = $this->lineItemFactory->createLineItem($order, $name, $grossAmount, $taxRuleId, $context);
        $response = $this->creditService->doRequest(new AddCreditData($context, $order, [$lineItem]));
        if ($response->getResponse()->isSuccessful()) {
            return $this->json([
                'success' => true,
            ], 200);
        }

        return $this->json([
            'success' => false,
            'message' => $response->getResponse()->getReasonMessage(),
        ], 200);
    }

    protected function fetchOrder(Context $context, string $orderId): ?OrderEntity
    {
        return $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderId), $context)->first();
    }
}
