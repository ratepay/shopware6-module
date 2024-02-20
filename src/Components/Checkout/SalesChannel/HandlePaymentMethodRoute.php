<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\SalesChannel;

use Ratepay\RpayPayments\Components\Checkout\Service\DataValidationService;
use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\SalesChannel\AbstractHandlePaymentMethodRoute;
use Shopware\Core\Checkout\Payment\SalesChannel\HandlePaymentMethodRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class HandlePaymentMethodRoute extends AbstractHandlePaymentMethodRoute
{
    public function __construct(
        private readonly AbstractHandlePaymentMethodRoute $innerService,
        private readonly DataValidationService $dataValidationService,
        private readonly EntityRepository $orderRepository
    ) {
    }

    public function getDecorated(): AbstractHandlePaymentMethodRoute
    {
        return $this->innerService;
    }

    public function load(Request $request, SalesChannelContext $context): HandlePaymentMethodRouteResponse
    {
        if ($request->headers->count() === 0) {
            // it seems like that this is not an API request. This should be an internal call of the route.
            // the module should only handle API calls.
            return $this->innerService->load($request, $context);
        }

        $paymentHandlerIdentifier = null;
        if ($request->request->getBoolean('updatePayment')) {
            $orderId = $request->request->get('orderId');

            $order = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderId), $context->getContext())->first();
            if ($order instanceof OrderEntity && ($transaction = $order->getTransactions()->last()) instanceof OrderTransactionEntity) {
                $paymentHandlerIdentifier = $transaction->getPaymentMethod()->getHandlerIdentifier();
            }
        } else {
            $paymentHandlerIdentifier = $context->getPaymentMethod()->getHandlerIdentifier();
        }

        if ($paymentHandlerIdentifier !== null && is_subclass_of($paymentHandlerIdentifier, AbstractPaymentHandler::class)) {
            $this->dataValidationService->validatePaymentData(new ParameterBag($request->request->all()), $order ?? $context);
        }

        return $this->innerService->load($request, $context);
    }
}
