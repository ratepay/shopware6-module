<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Service\Search;

use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileConfigSearch;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\Collection\ProfileConfigCollection;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProfileBySalesChannelContext implements ProfileSearchInterface
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly ProfileBySalesChannelContextAndCart $searchService
    ) {
    }

    public function createSearchObject(SalesChannelContext $salesChannelContext): ?ProfileConfigSearch
    {
        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        return $this->searchService->createSearchObject($salesChannelContext, $cart);
    }

    public function search(ProfileConfigSearch $profileConfigSearch): ProfileConfigCollection
    {
        return $this->searchService->search($profileConfigSearch);
    }
}
