<?php declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Account\Controller;

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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class AccountOrderControllerDecorator
{

    /**
     * @var \Shopware\Storefront\Controller\AccountOrderController
     */
    private AccountOrderController $innerService;

    /**
     * @var \Shopware\Core\Framework\DataAbstractionLayer\EntityRepository
     */
    private EntityRepository $orderRepository;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private RouterInterface $router;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private SessionInterface $session;


    public function __construct(
        AccountOrderController $innerService,
        EntityRepository $orderRepository,
        RouterInterface $router,
        SessionInterface $session
    )
    {
        $this->innerService = $innerService;
        $this->orderRepository = $orderRepository;
        $this->router = $router;
        $this->session = $session;
    }

    public function updateOrder(string $orderId, Request $request, SalesChannelContext $context): Response
    {
        $orderCriteria = (new Criteria([$orderId]))
            ->addAssociation('transactions.paymentMethod');
        $order = $this->orderRepository->search($orderCriteria, $context->getContext())->first();
        /** @var RatepayOrderDataEntity $ratepayData */
        $ratepayData = $order ? $order->getExtension(OrderExtension::EXTENSION_NAME) : null;
        if ($ratepayData && MethodHelper::isRatepayOrder($order) && $ratepayData->isSuccessful()) {
            // You can't change the payment if it is a ratepay order
            return new RedirectResponse($this->router->generate('frontend.account.edit-order.page', ['orderId' => $orderId]));
        }

        $this->addRatepayValidationErrors($request);
        return $this->innerService->updateOrder($orderId, $request, $context);
    }

    public function editOrder(string $orderId, Request $request, SalesChannelContext $context): Response
    {
        $this->addRatepayValidationErrors($request);
        return $this->innerService->editOrder($orderId, $request, $context);
    }

    protected function addRatepayValidationErrors(Request $request)
    {
        foreach ($request->get('ratepay-errors', []) as $error) {
            $this->session->getFlashBag()->add('danger', $error);
        }
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
}
