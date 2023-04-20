<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\AdminOrders\Controller;

use DateTime;
use Exception;
use Ratepay\RpayPayments\Components\AdminOrders\Model\RatepayAdminOrderTokenEntity;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class StorefrontController extends AbstractController
{
    private EntityRepository $tokenRepository;

    private string $sessionKey;

    private Translator $translator;

    public function __construct(
        EntityRepository $tokenRepository,
        Translator $translator,
        string $sessionKey
    ) {
        $this->tokenRepository = $tokenRepository;
        $this->sessionKey = $sessionKey;
        $this->translator = $translator;
    }

    /**
     * @Route("/ratepay-admin-login/{token}", name="ratepay.frontend.admin-login", methods={"GET"})
     */
    public function login(Request $request, SalesChannelContext $context): Response
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter(RatepayAdminOrderTokenEntity::FIELD_TOKEN, $request->get('token')));
        $criteria->addFilter(new EqualsFilter(RatepayAdminOrderTokenEntity::FIELD_SALES_CHANNEL_ID, $context->getSalesChannelId()));
        $criteria->addFilter(new EqualsFilter(RatepayAdminOrderTokenEntity::FIELD_CART_TOKEN, null));
        $criteria->addFilter(new RangeFilter(RatepayAdminOrderTokenEntity::FIELD_VAlID_UNTIL, [
            RangeFilter::GTE => (new DateTime())->format('Y-m-d H:i:s'),
        ]));
        $criteria->setLimit(1);

        $result = $this->tokenRepository->search($criteria, $context->getContext());

        if ($result->getTotal() !== 1) {
            $this->addFlash('danger', 'Invalid token to start a Ratepay storefront order!');

            return $this->redirectToRoute('frontend.home.page');
        }

        /** @var RatepayAdminOrderTokenEntity $tokenEntity */
        $tokenEntity = $result->first();
        try {
            $this->tokenRepository->update([
                [
                    RatepayAdminOrderTokenEntity::FIELD_ID => $tokenEntity->getId(),
                    RatepayAdminOrderTokenEntity::FIELD_CART_TOKEN => $context->getToken(),
                ],
            ], $context->getContext());

            $request->getSession()->set($this->sessionKey, true);
        } catch (Exception $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('frontend.home.page');
    }

    /**
     * @Route(path="/logout/", name="ratepay.frontend.admin-logout")
     */
    public function logout(Request $request): RedirectResponse
    {
        $request->getSession()->set($this->sessionKey, false);
        $this->addFlash('success', $this->translator->trans('ratepay.storefront.admin-order.session-destroyed'));

        return $this->redirectToRoute('frontend.home.page');
    }
}
