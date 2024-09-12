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
use Ratepay\RpayPayments\Core\Entity\Extension\OrderExtension;
use Ratepay\RpayPayments\Core\Entity\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\SalesChannel\AbstractHandlePaymentMethodRoute;
use Shopware\Core\Checkout\Payment\SalesChannel\HandlePaymentMethodRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

class HandlePaymentMethodRoute extends AbstractHandlePaymentMethodRoute
{
    public function __construct(
        private readonly AbstractHandlePaymentMethodRoute $innerService,
        private readonly DataValidationService $dataValidationService,
        private readonly EntityRepository $orderRepository,
        private readonly EntityRepository $ratepayDataRepository
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

        $orderId = $request->request->getAlnum('orderId');

        $paymentHandlerIdentifier = null;
        $order = null;
        if (!empty($orderId)) {
            /** @var OrderEntity|null $order */
            $order = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderId), $context->getContext())->first();
            if ($order instanceof OrderEntity && ($transaction = $order->getTransactions()->last()) instanceof OrderTransactionEntity) {
                $paymentHandlerIdentifier = $transaction->getPaymentMethod()->getHandlerIdentifier();
            }
        } else {
            $paymentHandlerIdentifier = $context->getPaymentMethod()->getHandlerIdentifier();
        }

        if ($paymentHandlerIdentifier !== null && is_subclass_of($paymentHandlerIdentifier, AbstractPaymentHandler::class)) {
            $this->dataValidationService->validatePaymentData(new DataBag($request->request->all()), $context, $order ?? null);
        }

        $result = $this->innerService->load($request, $context);

        $ratepayData = $order?->getExtension(OrderExtension::EXTENSION_NAME);
        if ($ratepayData instanceof RatepayOrderDataEntity) {
            $orderCriteria = (new Criteria([$orderId]))
                ->addAssociation('transactions.paymentMethod');

            /** @var OrderEntity $order */
            $order = $this->orderRepository->search($orderCriteria, $context->getContext())->first();
            if (!MethodHelper::isRatepayOrder($order)) {
                // if it is not a ratepay order anymore, we delete existing ratepay-data
                try {
                    $this->ratepayDataRepository->delete([[
                        RatepayOrderDataEntity::FIELD_ID => $ratepayData->getId(),
                    ]], $context->getContext());
                } catch (Throwable) {
                    // catch any exception but not handle it.
                    // we won't break behaviour of third-party payment methods if deletion fails.
                    // it is not so bad if we keep the ratepay-data in the database.
                }
            }
        }

        return $result;
    }
}
