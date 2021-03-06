<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\AdminOrders\Controller;

use Ratepay\RpayPayments\Components\AdminOrders\Model\RatepayAdminOrderTokenEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Storefront\Framework\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @RouteScope(scopes={"administration"})
 * @Route("/api/ratepay/admin-order")
 */
class TokenController extends AbstractController
{
    private EntityRepositoryInterface $tokenRepository;

    private EntityRepositoryInterface $salesChannelDomainRepository;

    private RouterInterface $router;

    public function __construct(
        EntityRepositoryInterface $tokenRepository,
        EntityRepositoryInterface $salesChannelDomainRepository,
        Router $router
    ) {
        $this->tokenRepository = $tokenRepository;
        $this->salesChannelDomainRepository = $salesChannelDomainRepository;
        $this->router = $router;
    }

    /**
     * @Route("/login-token", name="ratepay.admin.admin-orders.token", methods={"POST"})
     */
    public function login(Request $request): Response
    {
        $context = Context::createDefaultContext();
        $salesChannelId = $request->request->get('salesChannelId', null);
        $salesChannelDomainId = $request->request->get('salesChannelDomainId', null);

        /** @var SalesChannelDomainEntity $saleChannelDomain */
        $saleChannelDomain = $this->salesChannelDomainRepository->search(new Criteria([$salesChannelDomainId]), $context)->first();
        if ($saleChannelDomain === null) {
            throw $this->createNotFoundException('sales channel domain not found');
        }

        $token = Uuid::randomHex();
        $this->tokenRepository->upsert([
            [
                RatepayAdminOrderTokenEntity::FIELD_TOKEN => $token,
                RatepayAdminOrderTokenEntity::FIELD_SALES_CHANNEL_ID => $salesChannelId,
                RatepayAdminOrderTokenEntity::FIELD_SALES_CHANNEL_DOMAIN_ID => $salesChannelDomainId,
                RatepayAdminOrderTokenEntity::FIELD_VAlID_UNTIL => (new \DateTime())->modify('+30 min'),
            ],
        ], $context);

        $urlInfo = parse_url($saleChannelDomain->getUrl());
        $routerContext = $this->router->getContext();
        $routerContext
            ->setScheme($urlInfo['scheme'])
            ->setHost($urlInfo['host']);

        if (isset($urlInfo['path']) && $urlInfo['path']) {
            $routerContext->setBaseUrl($urlInfo['path']);
        }

        $storefrontUrl = $this->router->generate('ratepay.frontend.admin-login', [
            'token' => $token,
        ], RouterInterface::ABSOLUTE_URL);

        return $this->json([
            'success' => true,
            'url' => $storefrontUrl,
        ]);
    }
}
