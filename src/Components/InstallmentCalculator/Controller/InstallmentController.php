<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Controller;

use Ratepay\RpayPayments\Components\Checkout\Util\BankAccountHolderHelper;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Model\InstallmentCalculatorContext;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Service\InstallmentService;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Routing\Annotation\LoginRequired;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/ratepay/installment")
 * @RouteScope(scopes={"storefront"})
 */
class InstallmentController extends StorefrontController
{
    private InstallmentService $installmentService;

    private EntityRepository $orderRepository;

    public function __construct(
        InstallmentService $installmentService,
        EntityRepository $orderRepository
    )
    {
        $this->installmentService = $installmentService;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @LoginRequired(allowGuest=true)
     * @Route(path="/calculate/{orderId}", methods={"GET"}, name="ratepay.storefront.installment.calculate", defaults={"XmlHttpRequest"=true})
     */
    public function calculateInstallment(Request $request, SalesChannelContext $salesChannelContext, $orderId = null): Response
    {
        $type = $request->query->get('type');
        $value = (int)$request->query->get('value');
        $value = $value ?: 1; // RATESWSX-186: fix that no "0" values can be provided

        if ($orderId) {
            /** @var \Shopware\Core\Checkout\Order\OrderEntity $order */
            $order = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderId), $salesChannelContext->getContext())->first();
            if ($order === null) {
                throw $this->createNotFoundException();
            }
        }

        $calcContext = (new InstallmentCalculatorContext($salesChannelContext, $type, $value))
            ->setPaymentMethodId($salesChannelContext->getPaymentMethod()->getId())
            ->setOrder($order ?? null);

        $vars = $this->installmentService->getInstallmentPlanTwigVars($calcContext);

        return $this->renderStorefront('@Storefront/storefront/installment-calculator/installment-plan.html.twig', [
            'ratepay' => [
                'installment' => $vars,
                'accountHolders' => BankAccountHolderHelper::getAvailableNames($salesChannelContext)
            ]
        ]);
    }
}
