<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\DeviceFingerprint;

use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

interface DfpServiceInterface
{
    public function generatedDfpId(Request $request, SalesChannelContext $salesChannelContext, ?OrderEntity $orderEntity = null): ?string;

    public function getDfpSnippet(Request $request, SalesChannelContext $salesChannelContext, ?OrderEntity $orderEntity = null): ?string;

    public function isDfpRequired(SalesChannelContext $salesChannelContext, ?OrderEntity $orderEntity = null): bool;
}
