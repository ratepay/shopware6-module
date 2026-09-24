<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Controller;

use Ratepay\RpayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RpayPayments\Components\Checkout\Service\SecciService;
use Ratepay\RpayPayments\Components\Checkout\Struct\PaymentDataResponse;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundHttpException;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Routing\StoreApiRouteScope;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPageLoader;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

#[\Symfony\Component\Routing\Attribute\Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
class StoreApiController extends AbstractCheckoutController
{
    public function __construct(
        private readonly ExtensionService           $extensionService,
        private readonly AccountEditOrderPageLoader $orderLoader
    )
    {
    }

    #[Route(
        path: '/store-api/ratepay/payment-data/{orderId}',
        name: 'store-api.ratepay.checkout.payment-data',
        defaults: [
            '_loginRequired' => true,
            '_loginRequiredAllowGuest' => true,
        ],
        methods: ['GET']
    )]
    public function getPaymentData(Request $request, SalesChannelContext $salesChannelContext, ?string $orderId = null): Response
    {
        try {
            if ($orderId) {
                $subRequest = new Request();
                $subRequest->request->set('orderId', $orderId);
                $page = $this->orderLoader->load($subRequest, $salesChannelContext);
                /** @var ArrayStruct|null $extension */
                $extension = $page->getExtension('ratepay');

                if ($extension === null) {
                    throw new HttpException(400, 'Ratepay payment method seems to be not selected.');
                }
            } else {
                $extension = $this->extensionService->buildPaymentDataExtension($salesChannelContext, null, $request);
            }

            return new PaymentDataResponse($extension);
        } catch (ProfileNotFoundException) {
            throw new ProfileNotFoundHttpException();
        }
    }

    public function getDecorated(): AbstractCheckoutController
    {
        throw new DecorationPatternException(self::class);
    }
}
