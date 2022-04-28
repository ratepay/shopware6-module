<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\ProfileConfig\Service;

use PHPUnit\Framework\TestCase;
use Ratepay\RpayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\PrepaymentPaymentHandler;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\Definition\ProfileConfigDefinition;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileBySalesChannelContext;
use Ratepay\RpayPayments\Tests\Mock\Model\AddressMock;
use Ratepay\RpayPayments\Tests\TestConfig;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressCollection;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\SalesChannelApiTestBehaviour;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class ProfileConfigServiceTest extends TestCase
{
    use IntegrationTestBehaviour;
    use SalesChannelApiTestBehaviour;

    private const CUSTOMER_ID = 'cd4fae8320be4f53825534151d29984e';

//    private const CUSTOMER_BILLING_ADDRESS_ID = '66d6bfb7aeb647dcbe397ad43f239d5c';
//    private const CUSTOMER_SHIPPING_ADDRESS_ID = '028fd84f82984a43a2888da520df12ca';

    /**
     * @var EntityRepositoryInterface
     */
    private $profileConfigRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $currencyRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $paymentMethodRepository;

    protected function setUp(): void
    {
        $context = Context::createDefaultContext();
        $container = $this->getContainer();
        /* @var EntityRepositoryInterface $profileRepository */
        $this->profileConfigRepository = $container->get(ProfileConfigDefinition::ENTITY_NAME . '.repository');
        $this->currencyRepository = $container->get('currency.repository');
        $this->paymentMethodRepository = $container->get('payment_method.repository');

        $profiles = [];
        foreach (TestConfig::$configuration as $keys => $_) {
            $profiles[] = [
                ProfileConfigEntity::FIELD_ID => TestConfig::getUuid($keys),
                ProfileConfigEntity::FIELD_PROFILE_ID => TestConfig::getProfileId($keys),
                ProfileConfigEntity::FIELD_SECURITY_CODE => TestConfig::getSecurityCode($keys),
                ProfileConfigEntity::FIELD_SANDBOX => true,
                ProfileConfigEntity::FIELD_SALES_CHANNEL_ID => Defaults::SALES_CHANNEL,
            ];
        }

        $this->profileConfigRepository->upsert($profiles, $context);
    }

    public function testGetProfileConfigBySalesChannel(): void
    {
        /** @var ProfileBySalesChannelContext $searchService */
        $searchService = $this->getContainer()->get(ProfileBySalesChannelContext::class);

        // init all profiles
        $profileConfigService->refreshProfileConfigs(TestConfig::getAllUuids());

        $salesChannelContext = $this->createSalesChannelContextMock(
            PrepaymentPaymentHandler::class,
            'EUR',
            'DE',
            'DE',
            true
        );
        $profileConfig = $profileConfigService->getProfileConfigBySalesChannel($salesChannelContext);
        self::assertNotNull($profileConfig);
        self::assertEquals($profileConfig->getProfileId(), TestConfig::getProfileId('DE'));

        $salesChannelContext = $this->createSalesChannelContextMock(
            InstallmentPaymentHandler::class,
            'EUR',
            'DE',
            'DE',
            true
        );
        $profileConfig = $profileConfigService->getProfileConfigBySalesChannel($salesChannelContext);
        self::assertNotNull($profileConfig);
        self::assertEquals($profileConfig->getProfileId(), TestConfig::getProfileId('DE'));

        $salesChannelContext = $this->createSalesChannelContextMock(
            InstallmentZeroPercentPaymentHandler::class,
            'EUR',
            'DE',
            'DE',
            true
        );
        $profileConfig = $profileConfigService->getProfileConfigBySalesChannel($salesChannelContext);
        self::assertNotNull($profileConfig);
        self::assertEquals($profileConfig->getProfileId(), TestConfig::getProfileId('DE', true));

        $salesChannelContext = $this->createSalesChannelContextMock(
            PrepaymentPaymentHandler::class,
            'EUR',
            'AT',
            'AT',
            true
        );
        $profileConfig = $profileConfigService->getProfileConfigBySalesChannel($salesChannelContext);
        self::assertNotNull($profileConfig);
        self::assertEquals($profileConfig->getProfileId(), TestConfig::getProfileId('AT'));

        $salesChannelContext = $this->createSalesChannelContextMock(
            PrepaymentPaymentHandler::class,
            'CHF',
            'CH',
            'CH',
            true
        );
        $profileConfig = $profileConfigService->getProfileConfigBySalesChannel($salesChannelContext);
        self::assertNotNull($profileConfig);
        self::assertEquals($profileConfig->getProfileId(), TestConfig::getProfileId('CH'));
    }

    public function testGetProfileConfigBySalesChannelFail(): void
    {
        /** @var ProfileConfigService $profileConfigService */
        $profileConfigService = $this->getContainer()->get(ProfileConfigService::class);

        // init all profiles (except the zero percent installments)
        $profileConfigService->refreshProfileConfigs(TestConfig::getAllUuids(false));

        $salesChannelContext = $this->createSalesChannelContextMock(
            InstallmentZeroPercentPaymentHandler::class,
            'EUR',
            'DE',
            'DE',
            true
        );
        $profileConfig = $profileConfigService->getProfileConfigBySalesChannel($salesChannelContext);
        self::assertNull($profileConfig, 'there should be no configuration found, cause the zero percent installment configuration has not been loaded, yet.');

        $salesChannelContext = $this->createSalesChannelContextMock(
            DebitPaymentHandler::class,
            'EUR',
            'DE',
            'DE',
            false
        );
        $profileConfig = $profileConfigService->getProfileConfigBySalesChannel($salesChannelContext);
        self::assertNull($profileConfig, 'there should be no configuration found, cause the profile ' . TestConfig::getProfileId('DE') . ' does not allow different addresses for debit payments');

        $salesChannelContext = $this->createSalesChannelContextMock(
            PrepaymentPaymentHandler::class,
            'EUR',
            'AT',
            'DE',
            false
        );
        $profileConfig = $profileConfigService->getProfileConfigBySalesChannel($salesChannelContext);
        self::assertNull($profileConfig, 'there should be no configuration found, cause the profile ' . TestConfig::getProfileId('AT') . ' does not allow the billing for country DE');

        $salesChannelContext = $this->createSalesChannelContextMock(
            PrepaymentPaymentHandler::class,
            'EUR',
            'DE',
            'AT',
            false
        );
        $profileConfig = $profileConfigService->getProfileConfigBySalesChannel($salesChannelContext);
        self::assertNull($profileConfig, 'there should be no configuration found, cause the profile ' . TestConfig::getProfileId('DE') . ' does not allow the shipping into the country AT');

        $salesChannelContext = $this->createSalesChannelContextMock(
            PrepaymentPaymentHandler::class,
            'EUR',
            'CH',
            'CH',
            true
        );
        $profileConfig = $profileConfigService->getProfileConfigBySalesChannel($salesChannelContext);
        self::assertNull($profileConfig, 'there should be no configuration found, cause the profile  ' . TestConfig::getProfileId('CH') . ' does not support the currency EUR.');

        $salesChannelContext = $this->createSalesChannelContextMock(
            DebitPaymentHandler::class,
            'CHF',
            'CH',
            'CH',
            true
        );
        $profileConfig = $profileConfigService->getProfileConfigBySalesChannel($salesChannelContext);
        self::assertNull($profileConfig, 'there should be no configuration found, cause the profile  ' . TestConfig::getProfileId('CH') . ' does not support the payment method ' . DebitPaymentHandler::class);
    }

    private function createSalesChannelContextMock(
        string $handlerIdentifier,
        string $currencyIso,
        string $billingCountryIso,
        string $shippingCountryIso,
        bool $sameAddress
    ): SalesChannelContext {
        $context = Context::createDefaultContext();

        $paymentMethod = $this->paymentMethodRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('handlerIdentifier', $handlerIdentifier))
                ->setLimit(1),
            $context
        )->first();

        // create customer
        $customer = new CustomerEntity();
        $customer->setId(self::CUSTOMER_ID);

        // create addresses
        $billingAddress = AddressMock::createBillingAddress($customer, $billingCountryIso);
        $shippingAddress = $sameAddress ? $billingAddress : AddressMock::createShippingAddress($customer, $shippingCountryIso);

        $customer->setActiveBillingAddress($billingAddress);
        $customer->setDefaultBillingAddressId($billingAddress->getId());
        $customer->setActiveShippingAddress($shippingAddress);
        $customer->setDefaultShippingAddressId($shippingAddress->getId());
        $customer->setAddresses(new CustomerAddressCollection());
        $customer->getAddresses()->set($billingAddress->getId(), $billingAddress);
        $customer->getAddresses()->set($shippingAddress->getId(), $shippingAddress);

        // create currency
        $currency = new CurrencyEntity();
        $currency->setIsoCode($currencyIso);

        // create salesChannel
        $salesChannel = new SalesChannelEntity();
        $salesChannel->setId(Defaults::SALES_CHANNEL);

        // create salesChannelContext
        $salesChannelContext = $this->createMock(SalesChannelContext::class);
        $salesChannelContext->method('getCurrency')->willReturn($currency);
        $salesChannelContext->method('getPaymentMethod')->willReturn($paymentMethod);
        $salesChannelContext->method('getCustomer')->willReturn($customer);
        $salesChannelContext->method('getSalesChannel')->willReturn($salesChannel);
        $salesChannelContext->method('getContext')->willReturn($context);

        return $salesChannelContext;
    }
}
