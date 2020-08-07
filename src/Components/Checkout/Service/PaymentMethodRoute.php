<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Service;


use Shopware\Core\Checkout\Payment\SalesChannel\AbstractPaymentMethodRoute;
use Shopware\Core\Checkout\Payment\SalesChannel\PaymentMethodRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class PaymentMethodRoute extends AbstractPaymentMethodRoute
{

    /**
     * @var AbstractPaymentMethodRoute
     */
    private $innerService;

    /**
     * @var PaymentFilterService
     */
    private $paymentFilterService;

    public function __construct(AbstractPaymentMethodRoute $innerService, PaymentFilterService $paymentFilterService)
    {
        $this->innerService = $innerService;
        $this->paymentFilterService = $paymentFilterService;
    }


    public function getDecorated(): AbstractPaymentMethodRoute
    {
        return $this;
    }

    public function load(Request $request, SalesChannelContext $context, ?Criteria $criteria = null): PaymentMethodRouteResponse
    {
        $response = $this->innerService->load($request, $context, $criteria);

        if ($request->query->getBoolean('onlyAvailable', false)) {
            $paymentMethods = $this->paymentFilterService->filterPaymentMethods($response->getPaymentMethods(), $context);
            return new PaymentMethodRouteResponse($paymentMethods);
        }

        return $response;
    }
}
