<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Service;

use Ratepay\RpayPayments\Util\CriteriaHelper;
use Shopware\Core\Checkout\Payment\SalesChannel\AbstractPaymentMethodRoute;
use Shopware\Core\Checkout\Payment\SalesChannel\PaymentMethodRoute as CorePaymentMethodRoute;
use Shopware\Core\Checkout\Payment\SalesChannel\PaymentMethodRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PaymentMethodRoute extends CorePaymentMethodRoute
{
    /**
     * @var AbstractPaymentMethodRoute
     */
    private $innerService;

    /**
     * @var PaymentFilterService
     */
    private $paymentFilterService;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        AbstractPaymentMethodRoute $innerService,
        PaymentFilterService $paymentFilterService,
        RequestStack $requestStack,
        EntityRepositoryInterface $orderRepository
    ) {
        $this->innerService = $innerService;
        $this->paymentFilterService = $paymentFilterService;
        $this->requestStack = $requestStack;
        $this->orderRepository = $orderRepository;
    }

    public function getDecorated(): AbstractPaymentMethodRoute
    {
        return $this;
    }

    public function load(Request $request, SalesChannelContext $salesChannelContext, ?Criteria $criteria = null): PaymentMethodRouteResponse
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
            $order = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderId), $salesChannelContext->getContext())->first();
        }

        if ($order || $request->query->getBoolean('onlyAvailable', false)) {
            $paymentMethods = $this->paymentFilterService->filterPaymentMethods($response->getPaymentMethods(), $salesChannelContext, $order);

            return new PaymentMethodRouteResponse($paymentMethods);
        }

        return $response;
    }
}
