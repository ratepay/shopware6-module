<?php

namespace Ratepay\RpayPayments\Components\ProfileConfig\Service\Search;

use RuntimeException;
use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileConfigSearch;
use Ratepay\RpayPayments\Components\ProfileConfig\Event\CreateProfileConfigCriteriaEvent;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\System\Country\CountryEntity;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProfileSearchService implements ProfileSearchInterface
{

    protected Context $context;

    private EntityRepository $countryRepository;

    private EntityRepository $repository;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityRepository $countryRepository,
        EntityRepository $repository
    )
    {
        $this->countryRepository = $countryRepository;
        $this->repository = $repository;
        $this->eventDispatcher = $eventDispatcher;

        $this->context = Context::createDefaultContext();
    }

    /**
     * @param ProfileConfigSearch $profileConfigSearch
     * @return EntitySearchResult<ProfileConfigEntity>
     */
    public function search(ProfileConfigSearch $profileConfigSearch): EntitySearchResult
    {
        $criteria = new Criteria();
        $criteria->addAssociation(ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS);

        $paymentMethodConfigFilters = [];

        // payment method
        $paymentMethodConfigFilters[] = new EqualsFilter(
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD_ID,
            $profileConfigSearch->getPaymentMethodId()
        );

        // different addresses
        if ($profileConfigSearch->isNeedsAllowDifferentAddress()) {
            $paymentMethodConfigFilters[] = new EqualsFilter(
                ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_ALLOW_DIFFERENT_ADDRESSES,
                true
            );
        }

        // billing country
        $criteria->addFilter(new EqualsAnyFilter(ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING, [$profileConfigSearch->getBillingCountryCode()]));

        // delivery country
        $criteria->addFilter(new EqualsAnyFilter(ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING, [$profileConfigSearch->getShippingCountryCode()]));

        // sales channel
        $criteria->addFilter(new EqualsFilter(ProfileConfigEntity::FIELD_SALES_CHANNEL_ID, $profileConfigSearch->getSalesChannelId()));

        // currency
        $criteria->addFilter(new EqualsAnyFilter(ProfileConfigEntity::FIELD_CURRENCY, [$profileConfigSearch->getCurrency()]));

        // status
        $criteria->addFilter(new EqualsFilter(ProfileConfigEntity::FIELD_STATUS, true));

        // b2b
        if ($profileConfigSearch->isB2b()) {
            $paymentMethodConfigFilters[] = new EqualsFilter(
                ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_ALLOW_B2B,
                true
            );
        }

        // total amount
        $paymentMethodConfigFilters[] = new RangeFilter(
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_LIMIT_MIN,
            [RangeFilter::LTE => $profileConfigSearch->getTotalAmount()]
        );

        $b2cRangeFilter = new RangeFilter(
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_LIMIT_MAX,
            [RangeFilter::GTE => $profileConfigSearch->getTotalAmount()]
        );
        if ($profileConfigSearch->isB2b()) {
            $paymentMethodConfigFilters[] = new OrFilter([
                new AndFilter([
                    new EqualsFilter(
                        ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_LIMIT_MAX_B2B,
                        null
                    ),
                    $b2cRangeFilter
                ]),
                new RangeFilter(
                    ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS . '.' . ProfileConfigMethodEntity::FIELD_LIMIT_MAX_B2B,
                    [RangeFilter::GTE => $profileConfigSearch->getTotalAmount()]
                )
            ]);
        } else {
            $paymentMethodConfigFilters[] = $b2cRangeFilter;
        }

        $criteria->addFilter(new AndFilter($paymentMethodConfigFilters));

        $this->eventDispatcher->dispatch(new CreateProfileConfigCriteriaEvent(
            $criteria,
            $profileConfigSearch->getPaymentMethodId(),
            $profileConfigSearch->getBillingCountryCode(),
            $profileConfigSearch->getShippingCountryCode(),
            $profileConfigSearch->getSalesChannelId(),
            $profileConfigSearch->getCurrency(),
            $profileConfigSearch->isNeedsAllowDifferentAddress(),
            $profileConfigSearch->isB2b(),
            $profileConfigSearch->getTotalAmount(),
            $this->context
        ));

//        TODO implement
//        $this->eventDispatcher->dispatch(new CreateProfileConfigCriteriaEvent($criteria, $profileConfigSearch, $this->context));

        return $this->repository->search($criteria, $this->context);
    }

    private function getCountryCode(string $uuid): string
    {
        /** @var CountryEntity $country */
        $country = $this->countryRepository->search(new Criteria([$uuid]), Context::createDefaultContext())->first();
        if ($country) {
            return $country->getIso();
        }

        throw new RuntimeException('Country ' . $uuid . ' does not exist');
    }


    public function getProfileConfigById(?string $profileConfigId): ?ProfileConfigEntity
    {
        return $this->repository->search(new Criteria([$profileConfigId]), $this->context)->first();
    }

}
