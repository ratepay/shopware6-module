<?php

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Controller;

use Ratepay\RpayPayments\Components\InstallmentCalculator\Model\InstallmentCalculatorContext;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Service\InstallmentService;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Struct\InstallmentCalculationResponse;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundHttpException;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class InstallmentRoute
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
     * @Route(path="/store-api/ratepay/installment/calculate/{orderId}", methods={"GET"}, name="store-api.checkout.ratepay.installment.calculate", defaults={"_loginRequired"=true, "_loginRequiredAllowGuest"=true})
     */
    public function calculateInstallment(Request $request, SalesChannelContext $salesChannelContext, $orderId = null): InstallmentCalculationResponse
    {
        $type = $request->query->get('type');
        $value = (int)$request->query->get('value');
        $value = $value ?: 1; // RATESWSX-186: fix that no "0" values can be provided

        if ($orderId) {
            $order = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderId), $salesChannelContext->getContext())->first();
            if (!$order instanceof OrderEntity) {
                throw new NotFoundHttpException();
            }
        }

        try {
            $calcContext = (new InstallmentCalculatorContext($salesChannelContext, $type, $value))
                ->setPaymentMethodId($salesChannelContext->getPaymentMethod()->getId())
                ->setOrder($order ?? null);

            $vars = $this->installmentService->getInstallmentPlanTwigVars($calcContext);

            return new InstallmentCalculationResponse($vars);
        } catch (ProfileNotFoundException $profileNotFoundException) {
            throw new ProfileNotFoundHttpException();
        }
    }
}
