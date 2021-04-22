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
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProfileConfigService
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var EntityRepositoryInterface
     */
    private $repository;

    /**
     * @var ProfileRequestService
     */
    private $profileRequestService;

    /**
     * @var EntityRepositoryInterface
     */
    private $methodConfigRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $methodConfigInstallmentRepository;

    /**
     * @var ProfileConfigResponseConverter
     */
    private $profileConfigResponseConverter;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityRepositoryInterface $repository,
        EntityRepositoryInterface $methodConfigRepository,
        EntityRepositoryInterface $methodConfigInstallmentRepository,
        ProfileRequestService $profileRequestService,
        ProfileConfigResponseConverter $profileConfigResponseConverter
    ) {
        $this->repository = $repository;
        $this->methodConfigRepository = $methodConfigRepository;
        $this->methodConfigInstallmentRepository = $methodConfigInstallmentRepository;
        $this->profileRequestService = $profileRequestService;
        $this->profileConfigResponseConverter = $profileConfigResponseConverter;
        $this->eventDispatcher = $eventDispatcher;

        $this->context = Context::createDefaultContext();
    }

    public function refreshProfileConfigs(array $ids)
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

    public function getProfileConfigBySalesChannel(
        SalesChannelContext $salesChannelContext,
        string $paymentMethodId = null
    ): ?ProfileConfigEntity {
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

        return $this->getProfileConfigDefaultParams(
            $paymentMethodId,
            $billingCountry,
            $shippingCountry,
            $salesChannelContext->getSalesChannel()->getId(),
            $salesChannelContext->getCurrency()->getIsoCode(),
            $billingAddress->getId() !== $shippingAddress->getId(),
            $salesChannelContext->getContext()
        );
    }

    public function getProfileConfigDefaultParams(
        string $paymentMethodId,
        string $billingCountryIso,
        string $shippingCountryIso,
        string $salesChannelId,
        string $currencyIso,
        bool $differentAddresses,
        Context $context
    ) {
        // TODO: Move this function to a repository

        $criteria = new Criteria();
        $criteria->addAssociation(ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS);

        // payment method
        $criteria->addFilter(new EqualsFilter(
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD_ID,
            $paymentMethodId
        ));

        // payment method
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
        return $this->repository->search($criteria, $context)->first();
    }

    public function getProfileConfigByOrderEntity(OrderEntity $order, string $paymentMethodId, Context $context): ?ProfileConfigEntity
    {
        $billingAddress = $order->getAddresses()->get($order->getBillingAddressId());
        $shippingAddress = $order->getAddresses()->get($order->getDeliveries()->first()->getShippingOrderAddressId());

        $billingCountry = $billingAddress->getCountry()->getIso();

        if ($shippingAddress) {
            $shippingCountry = $shippingAddress->getCountry()->getIso();
        } else {
            $shippingCountry = $billingCountry;
        }

        return $this->getProfileConfigDefaultParams(
            $paymentMethodId,
            $billingCountry,
            $shippingCountry,
            $order->getSalesChannelId(),
            $order->getCurrency()->getIsoCode(),
            $billingAddress->getId() !== $shippingAddress->getId(),
            $context
        );
    }
}
