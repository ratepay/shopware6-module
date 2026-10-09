<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Controller;

use Ratepay\RpayPayments\Components\AdminOrders\Service\SessionService;
use Ratepay\RpayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RpayPayments\Components\Checkout\Service\SecciService;
use Ratepay\RpayPayments\Components\Checkout\Struct\PaymentDataResponse;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundHttpException;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Routing\StoreApiRouteScope;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPageLoader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
class CheckoutController extends AbstractCheckoutController
{
    public function __construct(
        private readonly ExtensionService           $extensionService,
        private readonly AccountEditOrderPageLoader $orderLoader
    ) {
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

    #[Route(
        path: '/checkout/ratepay/secci',
        name: 'frontend.checkout.ratepay.secci',
        defaults: [
            PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID],
            '_loginRequired' => true,
            '_loginRequiredAllowGuest' => true,
            'XmlHttpRequest' => true,
        ],
        methods: ['POST'],

    )]
    public function triggerSecciDelivery(Request $request, Cart $cart, SalesChannelContext $salesChannelContext, SecciService $secciService): Response
    {
        $ratepayData = $request->request->all('ratepay');
        $deliveryMethod = $ratepayData['secciDeliveryType'] ?? null;
        $databag = new RequestDataBag(['paymentDetails' => ['ratepay' => $ratepayData]]);

        $responseData = [
            'deliveryMethod' => $deliveryMethod,
        ];

        if ($deliveryMethod === 'mail') {
            try {
                $result = $secciService->triggerSecciEmail($databag, $cart, $salesChannelContext);
                $responseData['success'] = $result !== null;
                $responseData['documentId'] = $result;
            } catch (Throwable $t) {
                $responseData['success'] = false;
                $responseData['exception'] = $t->getMessage();
            }
        } elseif ($deliveryMethod === 'pdf') {
            $document = $secciService->prepareSecciPdf($databag, $cart, $salesChannelContext);

            if ($document) {
                $responseData['success'] = true;
                $responseData['document'] = [
                    'id' => $document['id'],
                    'contentType' => $document['contentType'],
                    'data' => base64_encode($document['data'])
                ];
            } else {
                $responseData['success'] = false;
            }
        } else {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($responseData);
    }

    #[Route(
        path: '/checkout/ratepay/secci-admin',
        name: 'frontend.checkout.ratepay.secci-admin',
        defaults: [
            PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID],
            '_loginRequired' => true,
            '_loginRequiredAllowGuest' => true,
            'XmlHttpRequest' => true,
        ],
        methods: ['POST']
    )]
    public function secci(SessionService $sessionService, Request $request, Cart $cart, SalesChannelContext $salesChannelContext, SecciService $secciService): Response
    {
        if (!$sessionService->isAdminSession($salesChannelContext, $request->getSession())) {
            return new Response(status: Response::HTTP_UNAUTHORIZED);
        }

        $email = $request->request->get('motoSecciEmail');
        $ratepayData = $request->request->all('ratepay');
        $databag = new RequestDataBag(['paymentDetails' => ['ratepay' => $ratepayData]]);

        $result = $secciService->triggerMotoSecciEmail($email, $databag, $cart, $salesChannelContext);
        return new JsonResponse([
            'success' => $result !== null,
            'deliveryMethod' => 'moto',
            'documentId' => $result,
            'email' => $email,
        ]);
    }

    public function getDecorated(): AbstractCheckoutController
    {
        throw new DecorationPatternException(self::class);
    }
}
