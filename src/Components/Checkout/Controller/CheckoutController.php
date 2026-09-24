<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Controller;

use Ratepay\RpayPayments\Components\Checkout\Service\SecciService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Package('checkout')]
#[\Symfony\Component\Routing\Attribute\Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
class CheckoutController extends StorefrontController
{
    #[Route(
        path: '/checkout/ratepay/secci/mail',
        name: 'frontend.checkout.ratepay.secci.mail',
        defaults: [
            '_loginRequired' => true,
            '_loginRequiredAllowGuest' => true,
        ],
        methods: ['GET'],
    )]
    public function triggerSecciEmail(Cart $cart, SalesChannelContext $salesChannelContext, SecciService $secciService): Response
    {
        $secciService->triggerSecciEmail($cart, $salesChannelContext);
        return $this->redirectToRoute('frontend.checkout.confirm.page');
    }

    #[Route(
        path: '/checkout/ratepay/secci/pdf',
        name: 'frontend.checkout.ratepay.secci.pdf',
        defaults: [
            '_loginRequired' => true,
            '_loginRequiredAllowGuest' => true,
        ],
        methods: ['GET'],
    )]
    public function downloadSecciPdf(Cart $cart, SalesChannelContext $salesChannelContext, SecciService $secciService): Response
    {
        $document = $secciService->prepareSecciPdf($cart, $salesChannelContext);

        if ($document) {
            $response = new Response($document['data'], Response::HTTP_OK, [
                'Content-Type' => $document['contentType'],
            ]);
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                "{$document['id']}.pdf",
            ));

            return $response;
        }

        return $this->redirectToRoute('frontend.checkout.confirm.page');
    }

    public function getDecorated(): AbstractCheckoutController
    {
        throw new DecorationPatternException(self::class);
    }
}
