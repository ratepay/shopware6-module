<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\AdminOrders\Service;

use Ratepay\RpayPayments\Components\DeviceFingerprint\DfpServiceInterface;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class DfpService implements DfpServiceInterface
{
    public function __construct(
        private readonly DfpServiceInterface $decorated,
        private readonly RequestStack $requestStack,
        private readonly SessionService $sessionService
    ) {
    }

    public function generatedDfpId(Request $request, SalesChannelContext $salesChannelContext, ?OrderEntity $orderEntity = null): ?string
    {
        return $this->isDfpRequired($salesChannelContext, $orderEntity) ? $this->decorated->generatedDfpId($request, $salesChannelContext, $orderEntity) : null;
    }

    public function getDfpSnippet(Request $request, SalesChannelContext $salesChannelContext, ?OrderEntity $orderEntity = null): ?string
    {
        return $this->isDfpRequired($salesChannelContext, $orderEntity) ? $this->decorated->getDfpSnippet($request, $salesChannelContext, $orderEntity) : null;
    }

    public function isDfpRequired(SalesChannelContext $salesChannelContext, ?OrderEntity $orderEntity = null): bool
    {
        $session = $this->requestStack->getMainRequest()->getSession();
        if ($this->sessionService->isAdminSession($salesChannelContext, $session)) {
            return false;
        }

        return $this->decorated->isDfpRequired($salesChannelContext, $orderEntity);
    }
}
