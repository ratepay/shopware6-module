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
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\SalesChannel\AbstractPaymentMethodRoute;
use Shopware\Core\Checkout\Payment\SalesChannel\PaymentMethodRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PaymentMethodRoute extends AbstractPaymentMethodRoute
{
    public function __construct(
        private readonly AbstractPaymentMethodRoute $innerService,
        private readonly PaymentFilterService $paymentFilterService,
        private readonly RequestStack $requestStack,
        private readonly EntityRepository $orderRepository
    ) {
    }

    public function getDecorated(): AbstractPaymentMethodRoute
    {
        return $this->innerService;
    }

    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): PaymentMethodRouteResponse
    {
        $response = $this->innerService->load($request, $context, $criteria);

        $currentRequest = $this->requestStack->getCurrentRequest();
        if (!$currentRequest instanceof Request) {
            return $response;
        }

        // if the order id is set, the oder has been already placed, and the customer may tries to change/edit
        // the payment method. - e.g. in case of a failed payment
        $orderId = $currentRequest->get('orderId');
        $order = null;
        if ($orderId) {
            /** @var OrderEntity|null $order */
            $order = $this->orderRepository->search(
                CriteriaHelper::getCriteriaForOrder($orderId),
                $context->getContext()
            )->first();
        }

        $this->paymentFilterService->filterPaymentMethods(
            $response->getObject(),
            $context,
            $order
        );

        return $response;
    }
}
