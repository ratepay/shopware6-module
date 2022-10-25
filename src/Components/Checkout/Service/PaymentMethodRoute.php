<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Service;

use Ratepay\RpayPayments\Util\CriteriaHelper;
use Shopware\Core\Checkout\Payment\SalesChannel\AbstractPaymentMethodRoute;
use Shopware\Core\Checkout\Payment\SalesChannel\PaymentMethodRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PaymentMethodRoute extends AbstractPaymentMethodRoute
{
    private AbstractPaymentMethodRoute $innerService;

    private PaymentFilterService $paymentFilterService;

    private RequestStack $requestStack;

    private EntityRepository $orderRepository;

    /**
     * the interface has been deprecated, but shopware is using the Interface in a decorator for the repository.
     * so it will crash, if we are only using EntityRepository, cause an object of the decorator got injected into the constructor.
     *
     * After Shopware has removed the decorator, we can replace this by a normal definition
     * @var EntityRepository|\Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface
     * TODO remove comment on Shopware Version 6.5.0.0 & readd type int & change constructor argument type
     */
    private $paymentMethodRepository;

    public function __construct(
        AbstractPaymentMethodRoute $innerService,
        PaymentFilterService $paymentFilterService,
        RequestStack $requestStack,
        EntityRepository $orderRepository,
        $paymentMethodRepository
    ) {
        $this->innerService = $innerService;
        $this->paymentFilterService = $paymentFilterService;
        $this->requestStack = $requestStack;
        $this->orderRepository = $orderRepository;
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function getDecorated(): AbstractPaymentMethodRoute
    {
        return $this;
    }

    public function load(Request $request, SalesChannelContext $salesChannelContext, Criteria $criteria): PaymentMethodRouteResponse
    {
        $response = $this->innerService->load($request, $salesChannelContext, $criteria);

        $currentRequest = $this->requestStack->getCurrentRequest();
        if (!$currentRequest) {
            return $response;
        }

        // if the order id is set, the oder has been already placed, and the customer may tries to change/edit
        // the payment method. - e.g. in case of a failed payment
        $orderId = $currentRequest->get('orderId');
        $order = null;
        if ($orderId) {
            $order = $this->orderRepository->search(
                CriteriaHelper::getCriteriaForOrder($orderId),
                $salesChannelContext->getContext()
            )->first();
        }

        if ($order || $request->query->getBoolean('onlyAvailable') || $request->request->getBoolean('onlyAvailable')) {
            $paymentMethods = $this->paymentFilterService->filterPaymentMethods(
                $response->getPaymentMethods(),
                $salesChannelContext,
                $order
            );

            $criteria->setIds($paymentMethods->getIds());

            return $this->innerService->load($request, $salesChannelContext, $criteria);
        }

        return $response;
    }
}
