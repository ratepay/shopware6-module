<?php

declare(strict_types=1);

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
use RuntimeException;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Exception\InvalidRequestParameterException;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Route(
    path: '/api/_action/order/{orderId}/ratepay/',
    defaults: [
        '_routeScope' => ['administration'],
    ]
)]
class ProductPanel extends AbstractController
{
    /**
     * @var AbstractModifyRequest[]
     */
    private array $requestServicesByOperation = [];

    public function __construct(
        private readonly EntityRepository $orderRepository,
        PaymentDeliverService $paymentDeliverService,
        PaymentReturnService $paymentReturnService,
        PaymentCancelService $paymentCancelService,
        private readonly PaymentCreditService $creditService,
        private readonly LineItemFactory $lineItemFactory,
        private readonly DataValidator $dataValidator
    ) {
        $this->requestServicesByOperation = [
            OrderOperationData::OPERATION_DELIVER => $paymentDeliverService,
            OrderOperationData::OPERATION_CANCEL => $paymentCancelService,
            OrderOperationData::OPERATION_RETURN => $paymentReturnService,
        ];
    }

    #[Route(path: 'info', name: 'ratepay.order_management.product_panel.load', methods: ['GET'])]
    public function load(string $orderId, Context $context): JsonResponse
    {
        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('lineItems');

        $order = $this->orderRepository->search($criteria, $context)->first();

        if ($order instanceof OrderEntity) {
            $items = [];
            foreach ($order->getLineItems() as $lineItem) {
                $extension = $lineItem->getExtension(OrderLineItemExtension::EXTENSION_NAME);
                if ($extension instanceof RatepayOrderLineItemDataEntity) {
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

            $orderExtension = $order->getExtension(OrderExtension::EXTENSION_NAME);
            if ($orderExtension instanceof RatepayOrderDataEntity && $orderExtension->getShippingPosition()) {
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
                'data' => array_values($items),
            ], 200);
        }

        return $this->json([
            'success' => false,
            'message' => 'Order not found',
        ], 404);
    }

    #[Route(path: 'deliver', name: 'ratepay.order_management.product_panel.deliver', methods: ['POST'])]
    public function deliver(string $orderId, Request $request, Context $context): Response
    {
        return $this->processModify($request, $context, OrderOperationData::OPERATION_DELIVER, $orderId);
    }

    #[Route(path: 'cancel', name: 'ratepay.order_management.product_panel.cancel', methods: ['POST'])]
    public function cancel(string $orderId, Request $request, Context $context): Response
    {
        return $this->processModify($request, $context, OrderOperationData::OPERATION_CANCEL, $orderId);
    }

    #[Route(path: 'refund', name: 'ratepay.order_management.product_panel.return', methods: ['POST'])]
    public function return(string $orderId, Request $request, Context $context): Response
    {
        return $this->processModify($request, $context, OrderOperationData::OPERATION_RETURN, $orderId);
    }

    #[Route(path: 'addItem', name: 'ratepay.order_management.product_panel.add_item', methods: ['POST'])]
    public function addItem(string $orderId, Request $request, Context $context): Response
    {
        $order = $this->fetchOrder($context, $orderId);

        if (!$order instanceof OrderEntity) {
            throw $this->createNotFoundException('Order was not found');
        }

        // validate provided data
        $definition = new DataValidationDefinition();
        $definition->add('name', new NotBlank());
        $definition->add('grossAmount', new NotBlank(), new AtLeastOneOf([new GreaterThan(0), new LessThan(0)]));
        $definition->add('taxId', new NotBlank());

        $this->dataValidator->validate($request->request->all(), $definition);

        $lineItem = $this->lineItemFactory->createLineItem(
            $order,
            (string) $request->request->get('name'),
            (float) (string) $request->request->get('grossAmount'),
            (string) $request->request->get('taxId'),
            $context
        );

        $response = $this->creditService->doRequest(new AddCreditData($context, $order, [$lineItem]));
        if (!$response->getResponse()->isSuccessful()) {
            return $this->json([
                'success' => false,
                'message' => $response->getResponse()->getReasonMessage(),
            ], 500);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    protected function processModify(Request $request, Context $context, string $operation, string $orderId): Response
    {
        $order = $this->fetchOrder($context, $orderId);

        if (!$order instanceof OrderEntity) {
            return $this->json([
                'success' => false,
                'message' => 'Order not found',
            ], 400);
        }

        $params = $request->request->all();
        if (!isset($params['items']) || !is_array($params['items'])) {
            if (class_exists(RoutingException::class) && method_exists(RoutingException::class, 'invalidRequestParameter')) {
                throw RoutingException::invalidRequestParameter('items');
            } elseif (class_exists(InvalidRequestParameterException::class)) {
                // required for shopware == 6.5.0.0
                throw new InvalidRequestParameterException('items');
            }

            // should never occur - just to be safe
            throw new RuntimeException('Invalid request parameter: items');
        }

        $items = [];
        foreach ($params['items'] as $data) {
            $items[$data['id']] = (int) $data['quantity'];
        }

        $items = array_filter($items, static fn ($quantity): bool => $quantity > 0);

        if ($items === []) {
            return $this->json([
                'success' => false,
                'message' => 'Please provide at least on item', // todo translation - should we translate it?
            ], 400); // todo is this status OK ?
        }

        $response = $this->requestServicesByOperation[$operation]->doRequest(
            new OrderOperationData($context, $order, $operation, $items, (bool) $request->request->get('updateStock'))
        );

        if (!$response->getResponse()->isSuccessful()) {
            return $this->json([
                'success' => false,
                'message' => $response->getResponse()->getReasonMessage(),
            ], 500);
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    protected function fetchOrder(Context $context, string $orderId): ?OrderEntity
    {
        return $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderId), $context)->first();
    }
}
