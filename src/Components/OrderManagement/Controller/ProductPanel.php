<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\OrderManagement\Controller;


use Ratepay\RatepayPayments\Components\OrderManagement\Util\LineItemUtil;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\AddCreditData;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\AbstractAddRequest;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\AbstractModifyRequest;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentCancelService;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentCreditService;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentDebitService;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentDeliverService;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentReturnService;
use Ratepay\RatepayPayments\Util\CriteriaHelper;
use Shopware\Core\Checkout\Cart\Order\RecalculationService;
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
     * @var PaymentDeliverService
     */
    private $paymentDeliverService;
    /**
     * @var PaymentReturnService
     */
    private $paymentReturnService;
    /**
     * @var PaymentCancelService
     */
    private $paymentCancelService;
    /**
     * @var EntityRepositoryInterface
     */
    private $lineItemRepository;
    /**
     * @var RecalculationService
     */
    private $recalculationService;
    /**
     * @var PaymentCreditService
     */
    private $creditService;

    /**
     * @var AbstractModifyRequest[]
     */
    private $requestServicesByOperation = [];

    public function __construct(
        EntityRepositoryInterface $orderRepository,
        EntityRepositoryInterface $lineItemRepository,
        RecalculationService $recalculationService,
        PaymentDeliverService $paymentDeliverService,
        PaymentReturnService $paymentReturnService,
        PaymentCancelService $paymentCancelService,
        PaymentCreditService $creditService
    )
    {
        $this->orderRepository = $orderRepository;
        $this->lineItemRepository = $lineItemRepository;
        $this->paymentDeliverService = $paymentDeliverService;
        $this->paymentReturnService = $paymentReturnService;
        $this->paymentCancelService = $paymentCancelService;
        $this->recalculationService = $recalculationService;
        $this->creditService = $creditService;

        $this->requestServicesByOperation = [
            OrderOperationData::OPERATION_DELIVER => $this->paymentDeliverService,
            OrderOperationData::OPERATION_CANCEL => $this->paymentCancelService,
            OrderOperationData::OPERATION_RETURN => $this->paymentReturnService
        ];
    }

    /**
     * @param string $orderId
     * @param Context $context
     * @RouteScope(scopes={"administration"})
     * @Route("/load/{orderId}", name="ratepay.order_management.product_panel.load", methods={"GET"})
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
                $items[$lineItem->getId()] = LineItemUtil::getLineItemArray($lineItem);
            }
            if ($shippingLineItem = LineItemUtil::getShippingLineItem($order)) {
                $items[$shippingLineItem['id']] = $shippingLineItem;
            }
            return $this->json([
                'success' => true,
                'data' => $items
            ], 200);
        } else {
            return $this->json([
                'success' => false,
                'message' => 'Order not found'
            ], 400);
        }
    }

    /**
     * @param string $orderId
     * @param Request $request
     * @param Context $context
     * @RouteScope(scopes={"administration"})
     * @Route("/deliver/{orderId}", name="ratepay.order_management.product_panel.deliver", methods={"POST"})
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
                $items[$data['id']] = (int)$data['quantity'];
            }

            $items = array_filter($items, function($quantity) {
                return $quantity > 0;
            });

            if(count($items) === 0) {
                return $this->json([
                    'success' => false,
                    'message' => 'Please provide at least on item' // todo translation - should we translate it?
                ], 200); // todo is this status OK ?
            }

            $response = $this->requestServicesByOperation[$operation]->doRequest(
                $context,
                new OrderOperationData($order, $operation, $items, $request->request->get('updateStock') == true)
            );

            return $this->json([
                'success' => $response->getResponse()->isSuccessful(),
                'message' => $response->getResponse()->getReasonMessage()
            ], 200);
        } else {
            return $this->json([
                'success' => false,
                'message' => 'Order not found'
            ], 400);
        }
    }

    /**
     * @param string $orderId
     * @param Request $request
     * @param Context $context
     * @RouteScope(scopes={"administration"})
     * @Route("/cancel/{orderId}", name="ratepay.order_management.product_panel.cancel", methods={"POST"})
     * @return JsonResponse
     */
    public function cancel($orderId, Request $request, Context $context)
    {
        return $this->processModify($request, $context, OrderOperationData::OPERATION_CANCEL, $orderId);
    }

    /**
     * @param string $orderId
     * @param Request $request
     * @param Context $context
     * @RouteScope(scopes={"administration"})
     * @Route("/return/{orderId}", name="ratepay.order_management.product_panel.return", methods={"POST"})
     * @return JsonResponse
     */
    public function return($orderId, Request $request, Context $context)
    {
        return $this->processModify($request, $context, OrderOperationData::OPERATION_RETURN, $orderId);
    }

    /**
     * @param string $orderId
     * @param Request $request
     * @param Context $context
     * @RouteScope(scopes={"administration"})
     * @Route("/addItem/{orderId}", name="ratepay.order_management.product_panel.add_item", methods={"POST"})
     * @return JsonResponse
     */
    public function addItem($orderId, Request $request, Context $context)
    {
        $amount = $request->request->get('amount');
        $label = $request->request->get('label');

        $order = $this->fetchOrder($context, $orderId);

        $response = $this->creditService->doRequest($context, new AddCreditData($order, $label, $amount));

        if ($response->getResponse()->isSuccessful()) {
            return $this->json([
                'success' => true
            ], 200);
        } else {
            return $this->json([
                'success' => false,
                'message' => $response->getResponse()->getReasonMessage()
            ], 200);
        }

    }

    /**
     * @param Context $context
     * @param string $orderId
     * @return OrderEntity|null
     */
    protected function fetchOrder(Context $context, $orderId)
    {
        return $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderId), $context)->first();
    }
}
