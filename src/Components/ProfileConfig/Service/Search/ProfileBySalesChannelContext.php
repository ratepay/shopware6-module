<?php

namespace Ratepay\RpayPayments\Components\ProfileConfig\Service\Search;

use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileConfigSearch;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Util\AddressUtil;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProfileBySalesChannelContext implements ProfileSearchInterface
{

    private CartService $cartService;
    private ProfileBySalesChannelContextAndCart $searchService;

    public function __construct(
        CartService $cartService,
        ProfileBySalesChannelContextAndCart $searchService
    )
    {
        $this->cartService = $cartService;
        $this->searchService = $searchService;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     * @return array<ProfileConfigEntity>|ProfileConfigEntity|null
     */
    public function createSearchObject(SalesChannelContext $salesChannelContext): ?ProfileConfigSearch
    {
        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        return $this->searchService->createSearchObject($salesChannelContext, $cart);
    }

    public function search(ProfileConfigSearch $profileConfigSearch): EntitySearchResult
    {
        return $this->searchService->search($profileConfigSearch);
    }

}
