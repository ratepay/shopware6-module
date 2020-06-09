<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\OrderManagement\Controller;


use Ratepay\RatepayPayments\Components\OrderManagement\Util\LineItemUtil;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\Request\AbstractModifyRequest;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\Request\PaymentCancelService;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\Request\PaymentDeliverService;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\Request\PaymentRequestService;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\Request\PaymentReturnService;
use Ratepay\RatepayPayments\Util\CriteriaHelper;
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

    public function __construct(
        EntityRepositoryInterface $orderRepository,
        PaymentDeliverService $paymentDeliverService,
        PaymentReturnService $paymentReturnService,
        PaymentCancelService $paymentCancelService
    )
    {
        $this->orderRepository = $orderRepository;
        $this->paymentDeliverService = $paymentDeliverService;
        $this->paymentReturnService = $paymentReturnService;
        $this->paymentCancelService = $paymentCancelService;
    }

    /**
     * @RouteScope(scopes={"administration"})
     * @Route("/load/{orderId}", name="ratepay.order_management.product_panel.load", methods={"GET"})
     * @return JsonResponse
     */
    public function load($orderId, Request $request, Context $context)
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

    protected function processModify(Request $request, Context $context, AbstractModifyRequest $service, $orderId) {
        /** @var OrderEntity $order */
        $order = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderId), $context)->first();

        if ($order) {
            $items = [];
            foreach($request->request->get('items') ?? [] as $data) {
                $items[$data['id']] = intval($data['quantity']);
            }

            $service->setTransaction($order);
            $service->setItems($items);
            $response = $service->doRequest();
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
     * @RouteScope(scopes={"administration"})
     * @Route("/deliver/{orderId}", name="ratepay.order_management.product_panel.deliver", methods={"POST"})
     * @return JsonResponse
     */
    public function deliver($orderId, Request $request, Context $context)
    {
        return $this->processModify($request, $context, $this->paymentDeliverService, $orderId);
    }

    /**
     * @RouteScope(scopes={"administration"})
     * @Route("/cancel/{orderId}", name="ratepay.order_management.product_panel.cancel", methods={"POST"})
     * @return JsonResponse
     */
    public function cancel($orderId, Request $request, Context $context)
    {
        $this->paymentCancelService->setUpdateStock($request->request->get('updateStock') == true);
        return $this->processModify($request, $context, $this->paymentCancelService, $orderId);
    }

    /**
     * @RouteScope(scopes={"administration"})
     * @Route("/return/{orderId}", name="ratepay.order_management.product_panel.return", methods={"POST"})
     * @return JsonResponse
     */
    public function return($orderId, Request $request, Context $context)
    {
        $this->paymentReturnService->setUpdateStock($request->request->get('updateStock') == true);
        return $this->processModify($request, $context, $this->paymentReturnService, $orderId);
    }
}
