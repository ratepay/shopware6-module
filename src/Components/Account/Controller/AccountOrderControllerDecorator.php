<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Account\Controller;

use Ratepay\RatepayPayments\Util\CriteriaHelper;
use Ratepay\RatepayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
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
        if (false && $order && MethodHelper::isRatepayOrder($order)) { // TODO add check: is payment failed
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
}
