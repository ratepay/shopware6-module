<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\InstallmentCalculator\Controller;


use Ratepay\RatepayPayments\Components\InstallmentCalculator\Service\InstallmentService;
use Shopware\Core\Framework\Context;
use Shopware\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/ratepay/installment")
 */
class InstallmentController extends AbstractController
{

    /**
     * @var InstallmentService
     */
    private $installmentService;

    public function __construct(
        InstallmentService $installmentService
    )
    {
        $this->installmentService = $installmentService;
    }

    /**
     * @Route(path="/calculate/", methods={"GET"}, name="ratepay.storefront.installment.calculate")
     */
    public function calculateInstallment(Request $request, Context $context)
    {
        $salesChannelContext = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
    }

}
