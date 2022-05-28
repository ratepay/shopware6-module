<?php

namespace Ratepay\RpayPayments\Tests\Components\ProfileConfig\Service\Search;

use PHPUnit\Framework\TestCase;
use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileConfigSearch;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileSearchService;
use Ratepay\RpayPayments\Tests\Traits\PaymentMethodTrait;
use Ratepay\RpayPayments\Tests\Traits\SalesChannelTrait;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * none of these test will test the content of the ProfileConfigEntity.
 * this tests will only check if the expected profile has been found by the service
 */
class ProfileSearchTest extends TestCase
{
    use IntegrationTestBehaviour;
    use SalesChannelTrait;
    use PaymentMethodTrait;

    private const PROFILE_COUNTRY_DE = 'profile-de-eur';
    private const PROFILE_COUNTRY_AT = 'profile-at-eur';
    private const PROFILE_COUNTRY_FRCHDE = 'profile-frchde-eur';
    private const PROFILE_COUNTRY_CH = 'profile-ch-chf';
    private const PROFILE_CH_EUR = 'profile-ch-eur';
    private const PROFILE_AMOUNT_1 = 'profile-amount-1';
    private const PROFILE_AMOUNT_2 = 'profile-amount-2';
    private const PROFILE_AMOUNT_3 = 'profile-amount-3';
    private const PROFILE_B2B = 'profile-b2b';
    private const PROFILE_NO_B2B = 'profile-no-b2b';
    private const PROFILE_STATUS_ENABLED = 'profile-enabled';
    private const PROFILE_STATUS_DISABLED = 'profile-disabled';
    private const PROFILE_DIFFERENT_ADDRESS = 'profile-need-different-address';
    private const PROFILE_NO_DIFFERENT_ADDRESS = 'profile-not-need-different-address';
    private const PROFILE_CURRENCY_EUR = 'currency-eur';
    private const PROFILE_CURRENCY_CHF = 'currency-chf';
    private const PROFILE_CURRENCY_EUR_USD = 'currency-eur-usd';

    public function testCountry()
    {
        $searchService = $this->getContainer()->get(ProfileSearchService::class);

        $searchEntity = (new ProfileConfigSearch())
            ->setSalesChannelId($this->getSalesChannel()->getId())
            ->setPaymentMethodId($this->getInvoiceEntity()->getId())
            ->setTotalAmount(150)
            ->setNeedsAllowDifferentAddress(false)
            ->setCurrency('TEST-COUNTRY');

        // test country DE
        $searchEntity->setBillingCountryCode('DE')
            ->setShippingCountryCode('DE');
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_COUNTRY_DE, self::PROFILE_COUNTRY_FRCHDE], $profileConfigs);

        // test country AT
        $searchEntity->setBillingCountryCode('AT')
            ->setShippingCountryCode('AT');
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_COUNTRY_AT], $profileConfigs);

        // test country CH/FR
        $searchEntity->setBillingCountryCode('CH')
            ->setShippingCountryCode('FR');
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_COUNTRY_FRCHDE], $profileConfigs);

        // test country FR/CH
        $searchEntity->setBillingCountryCode('FR')
            ->setShippingCountryCode('CH');
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_COUNTRY_FRCHDE], $profileConfigs);

        // test country FR/invalid (no result expected)
        $searchEntity->setBillingCountryCode('FR')
            ->setShippingCountryCode('--invalid--');
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([], $profileConfigs);

        // test country invalid/FR (no result expected)
        $searchEntity->setBillingCountryCode('--invalid--')
            ->setShippingCountryCode('FR');
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([], $profileConfigs);
    }

    public function testCurrency()
    {
        $searchService = $this->getContainer()->get(ProfileSearchService::class);

        $searchEntity = (new ProfileConfigSearch())
            ->setSalesChannelId($this->getSalesChannel()->getId())
            ->setPaymentMethodId($this->getInvoiceEntity()->getId())
            ->setTotalAmount(150)
            ->setNeedsAllowDifferentAddress(false)
            ->setBillingCountryCode('TEST-CURRENCY')
            ->setShippingCountryCode('TEST-CURRENCY');

        // test currency EUR
        $searchEntity->setCurrency('EUR');
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_CURRENCY_EUR, self::PROFILE_CURRENCY_EUR_USD], $profileConfigs);

        // test country CHF
        $searchEntity->setCurrency('CHF');
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_CURRENCY_CHF], $profileConfigs);

        // test country USD
        $searchEntity->setCurrency('USD');
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_CURRENCY_EUR_USD], $profileConfigs);

        // test invalid
        $searchEntity->setBillingCountryCode('invalid');
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([], $profileConfigs);
    }

    public function testAmount()
    {
        $searchService = $this->getContainer()->get(ProfileSearchService::class);

        $searchEntity = (new ProfileConfigSearch())
            ->setSalesChannelId($this->getSalesChannel()->getId())
            ->setPaymentMethodId($this->getInvoiceEntity()->getId())
            ->setNeedsAllowDifferentAddress(false)
            ->setBillingCountryCode('TEST-AMOUNT')
            ->setShippingCountryCode('TEST-AMOUNT')
            ->setCurrency('TEST-AMOUNT');

        // test amount: no results
        $searchEntity->setTotalAmount(20);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([], $profileConfigs);

        // test amount: one match
        $searchEntity->setTotalAmount(150);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_AMOUNT_3], $profileConfigs);

        // test amount: exact min
        $searchEntity->setTotalAmount(100);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_AMOUNT_3], $profileConfigs);

        // test amount: exact min - 1 (no-result)
        $searchEntity->setTotalAmount($searchEntity->getTotalAmount() - 1);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([], $profileConfigs);

        // test amount: exact max (result)
        $searchEntity->setTotalAmount(200);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_AMOUNT_3], $profileConfigs);

        // test amount:  ax exact + 1 (no-result)
        $searchEntity->setTotalAmount($searchEntity->getTotalAmount() + 1);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([], $profileConfigs);

        // test amount: b2b (below) max
        $searchEntity->setTotalAmount(9000)->setIsB2b(true);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_AMOUNT_2], $profileConfigs);
        $searchEntity->setIsB2b(false); // reset B2B to default

        // test amount: b2b max exact
        $searchEntity->setTotalAmount(10000)->setIsB2b(true);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_AMOUNT_2], $profileConfigs);
        $searchEntity->setIsB2b(false); // reset B2B to default

        // test amount: b2b max exact + 1 (no-result)
        $searchEntity->setTotalAmount($searchEntity->getTotalAmount() + 1)->setIsB2b(true);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([], $profileConfigs);
        $searchEntity->setIsB2b(false); // reset B2B to default

        // test amount: b2b no match cause B2B is not a
        $searchEntity->setTotalAmount($searchEntity->getTotalAmount() + 1)->setIsB2b(true);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([], $profileConfigs);
        $searchEntity->setIsB2b(false); // reset B2B to default

        // test amount: b2b no match cause B2B Limit is set to zero
        $searchEntity->setTotalAmount(450)->setIsB2b(true);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([], $profileConfigs);
        $searchEntity->setIsB2b(false); // reset B2B to default

        // test amount: between all
        $searchEntity->setTotalAmount(550);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_AMOUNT_1, self::PROFILE_AMOUNT_2], $profileConfigs);
    }

    public function testB2b()
    {
        $searchService = $this->getContainer()->get(ProfileSearchService::class);

        $searchEntity = (new ProfileConfigSearch())
            ->setSalesChannelId($this->getSalesChannel()->getId())
            ->setPaymentMethodId($this->getInvoiceEntity()->getId())
            ->setNeedsAllowDifferentAddress(false)
            ->setBillingCountryCode('TEST-B2B')
            ->setShippingCountryCode('TEST-B2B')
            ->setCurrency('TEST-B2B')
            ->setTotalAmount(500);

        // test b2b: is b2b
        $searchEntity->setIsB2b(true);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_B2B], $profileConfigs);

        // test b2b: is not b2b
        $searchEntity->setIsB2b(false);
        $profileConfigs = $searchService->search($searchEntity);
        // the b2b profile will be also returned, cause B2B is an additional feature of a profile
        self::assetProfileIds([self::PROFILE_NO_B2B, self::PROFILE_B2B], $profileConfigs);
    }

    public function testStatus()
    {
        $searchService = $this->getContainer()->get(ProfileSearchService::class);

        $searchEntity = (new ProfileConfigSearch())
            ->setSalesChannelId($this->getSalesChannel()->getId())
            ->setPaymentMethodId($this->getInvoiceEntity()->getId())
            ->setNeedsAllowDifferentAddress(false)
            ->setBillingCountryCode('TEST-STATUS')
            ->setShippingCountryCode('TEST-STATUS')
            ->setCurrency('TEST-STATUS')
            ->setTotalAmount(500);

        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_STATUS_ENABLED], $profileConfigs);
    }

    public function testDifferentAddresses()
    {
        $searchService = $this->getContainer()->get(ProfileSearchService::class);

        $searchEntity = (new ProfileConfigSearch())
            ->setSalesChannelId($this->getSalesChannel()->getId())
            ->setPaymentMethodId($this->getInvoiceEntity()->getId())
            ->setBillingCountryCode('TEST-DIFFERENT-ADDRESS')
            ->setShippingCountryCode('TEST-DIFFERENT-ADDRESS')
            ->setCurrency('TEST-DIFFERENT-ADDRESS')
            ->setTotalAmount(500);

        // test: is b2b
        $searchEntity->setNeedsAllowDifferentAddress(true);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_DIFFERENT_ADDRESS], $profileConfigs);

        // test: is not b2b
        $searchEntity->setNeedsAllowDifferentAddress(false);
        $profileConfigs = $searchService->search($searchEntity);
        self::assetProfileIds([self::PROFILE_NO_DIFFERENT_ADDRESS, self::PROFILE_DIFFERENT_ADDRESS], $profileConfigs);
    }

    protected function setUp(): void
    {
        $context = Context::createDefaultContext();
        /** @var EntityRepositoryInterface $configRepo */
        $configRepo = $this->getContainer()->get('ratepay_profile_config.repository');

        $configRepo->upsert([
            $this->getCountryProfile(self::PROFILE_COUNTRY_DE, ['DE'], ['DE']),
            $this->getCountryProfile(self::PROFILE_COUNTRY_AT, ['AT'], ['AT']),
            $this->getCountryProfile(self::PROFILE_COUNTRY_CH, ['CH'], ['CH']),
            $this->getCountryProfile(self::PROFILE_COUNTRY_FRCHDE, ['CH', 'FR', 'DE'], ['CH', 'FR', 'DE']),

            $this->getCurrencyProfile(self::PROFILE_CURRENCY_EUR, ['EUR']),
            $this->getCurrencyProfile(self::PROFILE_CURRENCY_CHF, ['CHF']),
            $this->getCurrencyProfile(self::PROFILE_CURRENCY_EUR_USD, ['EUR', 'USD']),

            $this->getAmountProfile(self::PROFILE_AMOUNT_1, 400, 600, 0),
            $this->getAmountProfile(self::PROFILE_AMOUNT_2, 500, 700, 10000),
            $this->getAmountProfile(self::PROFILE_AMOUNT_3, 100, 200, 0),

            $this->getB2BProfile(self::PROFILE_B2B, true),
            $this->getB2BProfile(self::PROFILE_NO_B2B, false),

            $this->getStatusProfile(self::PROFILE_STATUS_ENABLED, true),
            $this->getStatusProfile(self::PROFILE_STATUS_DISABLED, false),

            $this->getDifferentAddressProfile(self::PROFILE_DIFFERENT_ADDRESS, true),
            $this->getDifferentAddressProfile(self::PROFILE_NO_DIFFERENT_ADDRESS, false),
        ], $context);
    }

    private function getCountryProfile(string $profileId, array $billingCountries, array $shippingCountries): array
    {
        $salesChannel = $this->getSalesChannel();
        $entityId = Uuid::randomHex();
        return [
            ProfileConfigEntity::FIELD_ID => $entityId,
            ProfileConfigEntity::FIELD_PROFILE_ID => $profileId,
            ProfileConfigEntity::FIELD_SECURITY_CODE => '-',
            ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING => $billingCountries,
            ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING => $shippingCountries,
            ProfileConfigEntity::FIELD_CURRENCY => ['TEST-COUNTRY'],
            ProfileConfigEntity::FIELD_ONLY_ADMIN_ORDERS => false,
            ProfileConfigEntity::FIELD_STATUS => true,
            ProfileConfigEntity::FIELD_SANDBOX => true,
            ProfileConfigEntity::FIELD_SALES_CHANNEL_ID => $salesChannel->getId(),
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS => [
                $this->getDefaultPaymentMethodConfig($entityId, $this->getInvoiceEntity())
            ]
        ];
    }

    private function getCurrencyProfile(string $profileId, array $currencies): array
    {
        $salesChannel = $this->getSalesChannel();
        $entityId = Uuid::randomHex();
        return [
            ProfileConfigEntity::FIELD_ID => $entityId,
            ProfileConfigEntity::FIELD_PROFILE_ID => $profileId,
            ProfileConfigEntity::FIELD_SECURITY_CODE => '-',
            ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING => ['TEST-CURRENCY'],
            ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING => ['TEST-CURRENCY'],
            ProfileConfigEntity::FIELD_CURRENCY => $currencies,
            ProfileConfigEntity::FIELD_ONLY_ADMIN_ORDERS => false,
            ProfileConfigEntity::FIELD_STATUS => true,
            ProfileConfigEntity::FIELD_SANDBOX => true,
            ProfileConfigEntity::FIELD_SALES_CHANNEL_ID => $salesChannel->getId(),
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS => [
                $this->getDefaultPaymentMethodConfig($entityId, $this->getInvoiceEntity())
            ]
        ];
    }

    private function getAmountProfile(string $profileId, float $min, float $max, float $maxB2b): array
    {
        $uuid = Uuid::randomHex();
        return [
            ProfileConfigEntity::FIELD_ID => $uuid,
            ProfileConfigEntity::FIELD_PROFILE_ID => $profileId,
            ProfileConfigEntity::FIELD_SECURITY_CODE => '-',
            ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING => ['TEST-AMOUNT'],
            ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING => ['TEST-AMOUNT'],
            ProfileConfigEntity::FIELD_CURRENCY => ['TEST-AMOUNT'],
            ProfileConfigEntity::FIELD_ONLY_ADMIN_ORDERS => false,
            ProfileConfigEntity::FIELD_STATUS => true,
            ProfileConfigEntity::FIELD_SANDBOX => true,
            ProfileConfigEntity::FIELD_SALES_CHANNEL_ID => $this->getSalesChannel()->getId(),
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS => [
                $this->getDefaultPaymentMethodConfig(
                    $uuid,
                    $this->getInvoiceEntity(),
                    $min,
                    $max,
                    $maxB2b
                )
            ]
        ];
    }

    private function getB2BProfile(string $profileId, bool $isB2b): array
    {
        $uuid = Uuid::randomHex();
        $methodConfig = $this->getDefaultPaymentMethodConfig(
            $uuid,
            $this->getInvoiceEntity()
        );
        $methodConfig[ProfileConfigMethodEntity::FIELD_ALLOW_B2B] = $isB2b;

        return [
            ProfileConfigEntity::FIELD_ID => $uuid,
            ProfileConfigEntity::FIELD_PROFILE_ID => $profileId,
            ProfileConfigEntity::FIELD_SECURITY_CODE => '-',
            ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING => ['TEST-B2B'],
            ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING => ['TEST-B2B'],
            ProfileConfigEntity::FIELD_CURRENCY => ['TEST-B2B'],
            ProfileConfigEntity::FIELD_ONLY_ADMIN_ORDERS => false,
            ProfileConfigEntity::FIELD_STATUS => true,
            ProfileConfigEntity::FIELD_SANDBOX => true,
            ProfileConfigEntity::FIELD_SALES_CHANNEL_ID => $this->getSalesChannel()->getId(),
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS => [
                $methodConfig
            ]
        ];
    }

    private function getDifferentAddressProfile(string $profileId, bool $differentAllowed): array
    {
        $uuid = Uuid::randomHex();
        $methodConfig = $this->getDefaultPaymentMethodConfig(
            $uuid,
            $this->getInvoiceEntity()
        );
        $methodConfig[ProfileConfigMethodEntity::FIELD_ALLOW_DIFFERENT_ADDRESSES] = $differentAllowed;

        return [
            ProfileConfigEntity::FIELD_ID => $uuid,
            ProfileConfigEntity::FIELD_PROFILE_ID => $profileId,
            ProfileConfigEntity::FIELD_SECURITY_CODE => '-',
            ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING => ['TEST-DIFFERENT-ADDRESS'],
            ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING => ['TEST-DIFFERENT-ADDRESS'],
            ProfileConfigEntity::FIELD_CURRENCY => ['TEST-DIFFERENT-ADDRESS'],
            ProfileConfigEntity::FIELD_ONLY_ADMIN_ORDERS => false,
            ProfileConfigEntity::FIELD_STATUS => true,
            ProfileConfigEntity::FIELD_SANDBOX => true,
            ProfileConfigEntity::FIELD_SALES_CHANNEL_ID => $this->getSalesChannel()->getId(),
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS => [
                $methodConfig
            ]
        ];
    }

    private function getStatusProfile(string $profileId, bool $enabled): array
    {
        $uuid = Uuid::randomHex();

        return [
            ProfileConfigEntity::FIELD_ID => $uuid,
            ProfileConfigEntity::FIELD_PROFILE_ID => $profileId,
            ProfileConfigEntity::FIELD_SECURITY_CODE => '-',
            ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING => ['TEST-STATUS'],
            ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING => ['TEST-STATUS'],
            ProfileConfigEntity::FIELD_CURRENCY => ['TEST-STATUS'],
            ProfileConfigEntity::FIELD_ONLY_ADMIN_ORDERS => false,
            ProfileConfigEntity::FIELD_STATUS => $enabled,
            ProfileConfigEntity::FIELD_SANDBOX => true,
            ProfileConfigEntity::FIELD_SALES_CHANNEL_ID => $this->getSalesChannel()->getId(),
            ProfileConfigEntity::FIELD_PAYMENT_METHOD_CONFIGS => [
                $this->getDefaultPaymentMethodConfig(
                    $uuid,
                    $this->getInvoiceEntity()
                )
            ]
        ];
    }

    private function getDefaultPaymentMethodConfig(
        string $profileUuid,
        PaymentMethodEntity $paymentMethodEntity,
        float $min = 1,
        float $max = 9999,
        float $maxB2b = 9999
    ): array
    {
        return [
            ProfileConfigMethodEntity::FIELD_PROFILE_ID => $profileUuid,
            ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD_ID => $paymentMethodEntity->getId(),
            ProfileConfigMethodEntity::FIELD_LIMIT_MIN => $min,
            ProfileConfigMethodEntity::FIELD_LIMIT_MAX => $max,
            ProfileConfigMethodEntity::FIELD_LIMIT_MAX_B2B => $maxB2b,
            ProfileConfigMethodEntity::FIELD_ALLOW_B2B => true,
            ProfileConfigMethodEntity::FIELD_ALLOW_DIFFERENT_ADDRESSES => true,
        ];
    }

    private static function assetProfileIds(array $expectedProfileIds, EntitySearchResult $actualResult)
    {
        self::assertCount(count($expectedProfileIds), $actualResult->getElements());

        $actualProfileIds = array_map(static function (ProfileConfigEntity $entity) {
            return $entity->getProfileId();
        }, $actualResult->getElements());

        foreach ($expectedProfileIds as $expectedProfileId) {
            self::assertContains($expectedProfileId, $actualProfileIds);
        }
    }
}
