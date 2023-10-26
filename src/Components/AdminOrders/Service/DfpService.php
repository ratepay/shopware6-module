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
        private readonly string $sessionKey
    ) {
    }

    public function generatedDfpId(Request $request, OrderEntity|SalesChannelContext $baseData): ?string
    {
        return $this->isDfpRequired($baseData) ? $this->decorated->generatedDfpId($request, $baseData) : null;
    }

    public function getDfpSnippet(Request $request, OrderEntity|SalesChannelContext $baseData): ?string
    {
        return $this->isDfpRequired($baseData) ? $this->decorated->getDfpSnippet($request, $baseData) : null;
    }

    public function isDfpIdValid(OrderEntity|SalesChannelContext $baseData, string $dfpId = null): bool
    {
        return !$this->isDfpRequired($baseData) || $this->decorated->isDfpIdValid($baseData, $dfpId);
    }

    public function isDfpRequired(OrderEntity|SalesChannelContext $object): bool
    {
        $session = $this->requestStack->getMainRequest()->getSession();
        if ($session->get($this->sessionKey)) {
            return false;
        }

        return $this->decorated->isDfpRequired($object);
    }
}
