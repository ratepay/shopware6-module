<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Account\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Exception;
use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\AccountOrderController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class AccountOrderControllerDecorator
{
    public function __construct(
        private readonly AccountOrderController $innerService,
        private readonly EntityRepository $orderRepository,
        private readonly RouterInterface $router,
        private readonly EntityRepository $ratepayDataRepository
    ) {
    }

    public function updateOrder(string $orderId, Request $request, SalesChannelContext $context): Response
    {
        $orderCriteria = (new Criteria([$orderId]))
            ->addAssociation('transactions.paymentMethod');
        $order = $this->orderRepository->search($orderCriteria, $context->getContext())->first();
        /** @var RatepayOrderDataEntity|null $ratepayData */
        $ratepayData = $order instanceof Entity ? $order->getExtension(OrderExtension::EXTENSION_NAME) : null;
        if ($ratepayData && MethodHelper::isRatepayOrder($order)) {
            if ($ratepayData->isSuccessful()) {
                // You can't change the payment if it is a sucessful ratepay order
                return new RedirectResponse($this->router->generate('frontend.account.edit-order.page', [
                    'orderId' => $orderId,
                ]));
            }

            $this->addRatepayValidationErrors($request);
        }

        $return = $this->innerService->updateOrder($orderId, $request, $context);

        // check again, if the order is now NOT a ratepay order.
        // if the order has been failed, the customer can switch between the payment methods.
        // after the updateOrder the payment method may not the same as before.
        $order = $this->orderRepository->search($orderCriteria, $context->getContext())->first();
        if ($ratepayData && !MethodHelper::isRatepayOrder($order)) {
            try {
                $event = $this->ratepayDataRepository->delete([[
                    RatepayOrderDataEntity::FIELD_ID => $ratepayData->getId(),
                ]], $context->getContext());
            } catch (Exception) {
                // catch any exception but not handle it.
                // we won't break behaviour of third-party payment methods if deletion fails.
                // it is not so bad if we keep the ratepay-data in the database.
            }
        }

        return $return;
    }

    public function editOrder(string $orderId, Request $request, SalesChannelContext $context): Response
    {
        $this->addRatepayValidationErrors($request);

        return $this->innerService->editOrder($orderId, $request, $context);
    }

    /* unchanged methods */

    public function orderChangePayment(string $orderId, Request $request, SalesChannelContext $context): Response
    {
        return $this->innerService->orderChangePayment($orderId, $request, $context);
    }

    public function orderOverview(Request $request, SalesChannelContext $context): Response
    {
        return $this->innerService->orderOverview($request, $context);
    }

    public function ajaxOrderDetail(Request $request, SalesChannelContext $context): Response
    {
        return $this->innerService->ajaxOrderDetail($request, $context);
    }

    public function cancelOrder(Request $request, SalesChannelContext $context): Response
    {
        return $this->innerService->cancelOrder($request, $context);
    }

    public function orderSingleOverview(Request $request, SalesChannelContext $context): Response
    {
        return $this->innerService->orderSingleOverview($request, $context);
    }

    protected function addRatepayValidationErrors(Request $request): void
    {
        foreach ($request->get('ratepay-errors', []) as $error) {
            if (($session = $request->getSession()) instanceof Session) {
                $session->getFlashBag()->add('danger', $error);
            }
        }
    }
}
