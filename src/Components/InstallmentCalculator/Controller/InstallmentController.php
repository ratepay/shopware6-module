<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Controller;

use Ratepay\RpayPayments\Components\Checkout\Util\BankAccountHolderHelper;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/ratepay/installment", defaults={"_routeScope"={"storefront"}})
 */
class InstallmentController extends StorefrontController
{
    public function __construct(
        private readonly InstallmentRoute $installmentRoute
    ) {
    }

    /**
     * @Route(path="/calculate/{orderId}", methods={"GET"}, name="ratepay.storefront.installment.calculate", defaults={"XmlHttpRequest"=true, "_loginRequired"=true, "_loginRequiredAllowGuest"=true})
     */
    public function calculateInstallment(Request $request, SalesChannelContext $salesChannelContext, ?string $orderId = null): Response
    {
        $vars = $this->installmentRoute->calculateInstallment($request, $salesChannelContext, $orderId);

        return $this->renderStorefront('@Storefront/storefront/installment-calculator/installment-plan.html.twig', [
            'ratepay' => [
                'installment' => [
                    'plan' => $vars->getPlan(),
                    'translations' => $vars->getTranslations(),
                    'transactionId' => $vars->getTransactionId(),
                ],
                'accountHolders' => BankAccountHolderHelper::getAvailableNames($salesChannelContext),
            ],
        ]);
    }
}
