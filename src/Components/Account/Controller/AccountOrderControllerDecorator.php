<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Account\Controller;

use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\AccountOrderController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class AccountOrderControllerDecorator extends AccountOrderController
{
    /**
     * @Route("/account/order/update/{orderId}", name="frontend.account.edit-order.update-order", methods={"POST"})
     */
    public function updateOrder(string $orderId, Request $request, SalesChannelContext $context): Response
    {
        $order = $this->fetchOrder($context->getContext(), $orderId);
        /** @var RatepayOrderDataEntity $ratepayData */
        $ratepayData = $order ? $order->getExtension(OrderExtension::EXTENSION_NAME) : null;
        if ($order && $ratepayData && MethodHelper::isRatepayOrder($order) && $ratepayData->isSuccessful()) {
            // You can't change the payment if it is a ratepay order
            return $this->redirectToRoute('frontend.account.edit-order.page', ['orderId' => $orderId]);
        }

        return parent::updateOrder($orderId, $request, $context);
    }

    protected function fetchOrder(Context $context, string $orderId): ?OrderEntity
    {
        $orderRepository = $this->container->get('order.repository');

        return $orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderId), $context)->first();
    }

    protected function renderStorefront(string $view, array $parameters = []): Response
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $formValidation = $request ? $request->attributes->get('formViolations') : null;
        if ($formValidation instanceof ConstraintViolationException) {
            $parameters['formViolations'] = $formValidation;
        }

        foreach ($request->get('ratepay-errors', []) as $error) {
            $this->addFlash('danger', $error);
        }

        return parent::renderStorefront($view, $parameters);
    }
}
