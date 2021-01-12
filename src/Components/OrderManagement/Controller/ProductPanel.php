<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\OrderManagement\Controller;

use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderLineItemExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderLineItemDataEntity;
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
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v{version}/ratepay/order-management")
 */
class ProductPanel extends AbstractController
{
    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PaymentCreditService
     */
    private $creditService;

    /**
     * @var AbstractModifyRequest[]
     */
    private $requestServicesByOperation;

    /**
     * @var OrderConverter
     */
    private $orderConverter;

    public function __construct(
        OrderConverter $orderConverter,
        EntityRepositoryInterface $orderRepository,
        PaymentDeliverService $paymentDeliverService,
        PaymentReturnService $paymentReturnService,
        PaymentCancelService $paymentCancelService,
        PaymentCreditService $creditService
    ) {
        $this->orderConverter = $orderConverter;
        $this->orderRepository = $orderRepository;
        $this->creditService = $creditService;

        $this->requestServicesByOperation = [
            OrderOperationData::OPERATION_DELIVER => $paymentDeliverService,
            OrderOperationData::OPERATION_CANCEL => $paymentCancelService,
            OrderOperationData::OPERATION_RETURN => $paymentReturnService,
        ];
    }

    /**
     * @param string $orderId
     * @RouteScope(scopes={"administration"})
     * @Route("/load/{orderId}", name="ratepay.order_management.product_panel.load", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function load($orderId, Context $context)
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
                $items['shipping'] = [
                    'id' => 'shipping',
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
        } else {
            return $this->json([
                'success' => false,
                'message' => 'Order not found',
            ], 400);
        }
    }

    /**
     * @param string $orderId
     * @RouteScope(scopes={"administration"})
     * @Route("/deliver/{orderId}", name="ratepay.order_management.product_panel.deliver", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function deliver($orderId, Request $request, Context $context)
    {
        return $this->processModify($request, $context, OrderOperationData::OPERATION_DELIVER, $orderId);
    }

    protected function processModify(Request $request, Context $context, string $operation, $orderId)
    {
        $order = $this->fetchOrder($context, $orderId);

        if ($order) {
            $items = [];
            foreach ($request->request->get('items') ?? [] as $data) {
                $items[$data['id']] = (int) $data['quantity'];
            }

            $items = array_filter($items, function ($quantity) {
                return $quantity > 0;
            });

            if (count($items) === 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'Please provide at least on item', // todo translation - should we translate it?
                ], 200); // todo is this status OK ?
            }

            $response = $this->requestServicesByOperation[$operation]->doRequest(
                $context,
                new OrderOperationData($order, $operation, $items, $request->request->get('updateStock') == true)
            );

            return $this->json([
                'success' => $response->getResponse()->isSuccessful(),
                'message' => $response->getResponse()->getReasonMessage(),
            ], 200);
        } else {
            return $this->json([
                'success' => false,
                'message' => 'Order not found',
            ], 400);
        }
    }

    /**
     * @param string $orderId
     * @RouteScope(scopes={"administration"})
     * @Route("/cancel/{orderId}", name="ratepay.order_management.product_panel.cancel", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function cancel($orderId, Request $request, Context $context)
    {
        return $this->processModify($request, $context, OrderOperationData::OPERATION_CANCEL, $orderId);
    }

    /**
     * @param string $orderId
     * @RouteScope(scopes={"administration"})
     * @Route("/return/{orderId}", name="ratepay.order_management.product_panel.return", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function return($orderId, Request $request, Context $context)
    {
        return $this->processModify($request, $context, OrderOperationData::OPERATION_RETURN, $orderId);
    }

    /**
     * @param string $orderId
     * @RouteScope(scopes={"administration"})
     * @Route("/addItem/{orderId}", name="ratepay.order_management.product_panel.add_item", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function addItem($orderId, Request $request, Context $context)
    {
        $name = $request->request->get('name');
        $grossAmount = $request->request->get('grossAmount');
        $taxRuleId = $request->request->get('taxId');

        $order = $this->fetchOrder($context, $orderId);

        $salesChannelContext = $this->orderConverter->assembleSalesChannelContext($order, $context);
        $taxRule = $salesChannelContext->getTaxRules()->get($taxRuleId)->getRules()->first();
        $response = $this->creditService->doRequest($context, new AddCreditData($order, $name, $grossAmount, $taxRule ? $taxRule->getTaxRate() : 0));

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

    /**
     * @param string $orderId
     *
     * @return OrderEntity|null
     */
    protected function fetchOrder(Context $context, $orderId)
    {
        return $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderId), $context)->first();
    }
}
