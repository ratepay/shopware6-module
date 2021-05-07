<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Controller;

use Ratepay\RpayPayments\Components\InstallmentCalculator\Service\InstallmentService;
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

    public function __construct(
        InstallmentService $installmentService
    ) {
        $this->installmentService = $installmentService;
    }

    /**
     * @LoginRequired()
     * @Route(path="/calculate/", methods={"GET"}, name="ratepay.storefront.installment.calculate", defaults={"XmlHttpRequest"=true})
     */
    public function calculateInstallment(Request $request, SalesChannelContext $context): Response
    {
        $type = $request->query->get('type');
        $value = $request->query->get('value');

        $installmentTranslations = $this->installmentService->getTranslations($context);
        $installmentPlan = $this->installmentService->getInstallmentPlanData($context, $type, $value);

        return $this->renderStorefront('@Storefront/storefront/installment-calculator/installment-plan.html.twig', [
            'installment' => [
                'translations' => $installmentTranslations,
                'plan' => $installmentPlan,
            ],
        ]);
    }
}
