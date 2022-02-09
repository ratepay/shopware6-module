<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Service;

use Ratepay\RpayPayments\Components\ProfileConfig\Event\CreateProfileConfigCriteriaEvent;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\Collection\ProfileConfigCollection;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\ProfileRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\ProfileRequestService;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProfileConfigService
{
    protected Context $context;

    private EntityRepositoryInterface $repository;

    private ProfileRequestService $profileRequestService;

    private EntityRepositoryInterface $methodConfigRepository;

    private EntityRepositoryInterface $methodConfigInstallmentRepository;

    private ProfileConfigResponseConverter $profileConfigResponseConverter;

    private EventDispatcherInterface $eventDispatcher;

    private CartService $cartService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityRepositoryInterface $repository,
        EntityRepositoryInterface $methodConfigRepository,
        EntityRepositoryInterface $methodConfigInstallmentRepository,
        ProfileRequestService $profileRequestService,
        ProfileConfigResponseConverter $profileConfigResponseConverter,
        CartService $cartService
    )
    {
        $this->repository = $repository;
        $this->methodConfigRepository = $methodConfigRepository;
        $this->methodConfigInstallmentRepository = $methodConfigInstallmentRepository;
        $this->profileRequestService = $profileRequestService;
        $this->profileConfigResponseConverter = $profileConfigResponseConverter;
        $this->eventDispatcher = $eventDispatcher;
        $this->cartService = $cartService;

        $this->context = Context::createDefaultContext();
    }

    public function refreshProfileConfigs(array $ids): EntitySearchResult
    {
        /** @var ProfileConfigCollection|ProfileConfigEntity[] $profileConfigs */
        $profileConfigs = $this->repository->search(new Criteria($ids), $this->context);

        foreach ($profileConfigs as $profileConfig) {
            $this->deleteMethodConfigsForProfile($profileConfig);

            $response = $this->profileRequestService->doRequest(
                new ProfileRequestData($this->context, $profileConfig)
            )->getResponse();

            [$profileConfigData, $methodConfigs, $installmentConfigs] = $this->profileConfigResponseConverter->convert(
                $response,
                $profileConfig->getId()
            );
            $profileConfigData[ProfileConfigEntity::FIELD_ID] = $profileConfig->getId();

            $this->repository->upsert([$profileConfigData], $this->context);

            if (isset($methodConfigs) && count($methodConfigs)) {
                $this->methodConfigRepository->upsert($methodConfigs, $this->context);
            }
            if (isset($installmentConfigs) && count($installmentConfigs)) {
                $this->methodConfigInstallmentRepository->upsert($installmentConfigs, $this->context);
            }
        }

        return $this->repository->search(CriteriaHelper::getCriteriaForProfileConfig($ids), $this->context);
    }

    protected function deleteMethodConfigsForProfile(ProfileConfigEntity $profileConfig): void
    {
        $entitiesToDelete = $this->methodConfigRepository->search(
            (new Criteria())->addFilter(new EqualsFilter(ProfileConfigMethodEntity::FIELD_PROFILE_ID, $profileConfig->getId())),
            $this->context
        );
        $deleteIds = $entitiesToDelete->getIds();
        if (count($deleteIds)) {
            $this->methodConfigRepository->delete(array_values(array_map(static function ($id) {
                return [
                    ProfileConfigMethodEntity::FIELD_ID => $id,
                ];
            }, $deleteIds)), $this->context);
        }
    }

    /**
     * @param \Shopware\Core\System\SalesChannel\SalesChannelContext $salesChannelContext
     * @param string|null $paymentMethodId
     * @param bool $single
     * @return array<ProfileConfigEntity>|ProfileConfigEntity|null
     */
    public function getProfileConfigBySalesChannel(
        SalesChannelContext $salesChannelContext,
        string $paymentMethodId = null,
        bool $single = true
    )
    {
        if (($customer = $salesChannelContext->getCustomer()) === null ||
            ($billingAddress = $customer->getActiveBillingAddress()) === null ||
            ($shippingAddress = $customer->getActiveShippingAddress()) === null
        ) {
            return null;
        }

        if ($paymentMethodId === null) {
            $paymentMethodId = $salesChannelContext->getPaymentMethod()->getId();
        }
        $billingCountry = $billingAddress->getCountry()->getIso();

        if ($shippingAddress) {
            $shippingCountry = $shippingAddress->getCountry()->getIso();
        } else {
            $shippingCountry = $billingCountry;
        }

        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);
        $resultList = $this->findProfileConfigsByDefaultParams(
            $paymentMethodId,
            $billingCountry,
            $shippingCountry,
            $salesChannelContext->getSalesChannel()->getId(),
            $salesChannelContext->getCurrency()->getIsoCode(),
            $billingAddress->getId() !== $shippingAddress->getId(),
            !empty($billingAddress->getCompany()),
            $cart->getPrice()->getTotalPrice(),
            $salesChannelContext->getContext()
        );

        return $single ? $resultList->first() : $resultList->getElements();
    }

    /**
     * @param \Shopware\Core\Checkout\Order\OrderEntity $order
     * @param string $paymentMethodId
     * @param \Shopware\Core\Framework\Context $context
     * @param bool $single
     * @return array<ProfileConfigEntity>|ProfileConfigEntity|null
     */
    public function getProfileConfigByOrderEntity(OrderEntity $order, string $paymentMethodId, Context $context, bool $single = true)
    {
        $billingAddress = $order->getAddresses()->get($order->getBillingAddressId());
        $shippingAddress = $order->getAddresses()->get($order->getDeliveries()->first()->getShippingOrderAddressId());

        $billingCountry = $billingAddress->getCountry()->getIso();

        if ($shippingAddress) {
            $shippingCountry = $shippingAddress->getCountry()->getIso();
        } else {
            $shippingCountry = $billingCountry;
        }

        $resultList = $this->findProfileConfigsByDefaultParams(
            $paymentMethodId,
            $billingCountry,
            $shippingCountry,
            $order->getSalesChannelId(),
            $order->getCurrency()->getIsoCode(),
            $billingAddress->getId() !== $shippingAddress->getId(),
            !empty($billingAddress->getCompany()),
            $order->getPrice()->getTotalPrice(),
            $context
        );

        return $single ? $resultList->first() : $resultList->getElements();
    }

    public function findProfileConfigsByDefaultParams(
        string $paymentMethodId,
        string $billingCountryIso,
        string $shippingCountryIso,
        string $salesChannelId,
        string $currencyIso,
        bool $differentAddresses,
        bool $isB2B,
        float $totalAmount,
        Context $context
    )
    {
        $criteria = new Criteria();
        $criteria->addAssociation(ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS);

        // payment method
        $criteria->addFilter(new EqualsFilter(
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD_ID,
            $paymentMethodId
        ));

        // different addresses
        if ($differentAddresses) {
            $criteria->addFilter(new EqualsFilter(
                ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_ALLOW_DIFFERENT_ADDRESSES,
                true
            ));
        }

        // billing country
        $criteria->addFilter(new EqualsAnyFilter(ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING, [$billingCountryIso]));

        // delivery country
        $criteria->addFilter(new EqualsAnyFilter(ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING, [$shippingCountryIso]));

        // sales channel
        $criteria->addFilter(new EqualsFilter(ProfileConfigEntity::FIELD_SALES_CHANNEL_ID, $salesChannelId));

        // currency
        $criteria->addFilter(new EqualsAnyFilter(ProfileConfigEntity::FIELD_CURRENCY, [$currencyIso]));

        // status
        $criteria->addFilter(new EqualsFilter(ProfileConfigEntity::FIELD_STATUS, true));

        // b2b
        if ($isB2B) {
            $criteria->addFilter(new EqualsFilter(
                ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_ALLOW_B2B,
                true
            ));
        }

        // total amount
        $criteria->addFilter(new RangeFilter(
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_LIMIT_MIN,
            [RangeFilter::LTE => $totalAmount]
        ));

        $b2cRangeFilter = new RangeFilter(
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_LIMIT_MAX,
            [RangeFilter::GTE => $totalAmount]
        );
        if ($isB2B) {
            $criteria->addFilter(new OrFilter([
                new AndFilter([
                    new EqualsFilter(
                        ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_LIMIT_MAX_B2B,
                        null
                    ),
                    $b2cRangeFilter
                ]),
                new RangeFilter(
                    ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_LIMIT_MAX_B2B,
                    [RangeFilter::GTE => $totalAmount]
                )
            ]));
        } else {
            $criteria->addFilter($b2cRangeFilter);
        }

        $this->eventDispatcher->dispatch(new CreateProfileConfigCriteriaEvent(
            $criteria,
            $paymentMethodId,
            $billingCountryIso,
            $shippingCountryIso,
            $salesChannelId,
            $currencyIso,
            $differentAddresses,
            $context
        ));

        return $this->repository->search($criteria, $context);
    }

    public function getProfileConfigById(?string $profileConfigId, Context $context): ProfileConfigEntity
    {
        return $this->repository->search(new Criteria([$profileConfigId]), $context)->first();
    }
}
