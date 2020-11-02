<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use DateTime;
use PHPUnit\Framework\TestCase;
use RatePAY\Model\Request\SubModel\Content\Customer;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Tests\Mock\Model\OrderAddressMock;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\Locale\LocaleEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CustomerFactoryTest extends TestCase
{
    use KernelTestBehaviour;

    public function testGetDataBaseData(): void
    {

        $requestData = $this->createRequestDataMock(
            'DE',
            'DE',
            true,
            false,
            false,
            true,
            false
        );

        $factory = $this->getContainer()->get(CustomerFactory::class);

        /** @var Customer $customer */
        $customer = $factory->getData($requestData);

        self::assertEquals('f', $customer->getGender());
        self::assertEquals('Frau', $customer->getSalutation());
        self::assertEquals('billing firstname', $customer->getFirstName());
        self::assertEquals('billing lastname', $customer->getLastName());
        self::assertEquals('de', $customer->getLanguage());
        self::assertEquals('123.456.789.123', $customer->getIpAddress());
        self::assertNull($customer->getCompanyName());
        self::assertNull($customer->getVatId());
        self::assertEquals('1980-12-30', $customer->getDateOfBirth(), 'birthday should be given, cause company is null');
        self::assertNull($customer->getContacts()->getPhone()->getAreaCode());
        self::assertEquals('0123456789', $customer->getContacts()->getPhone()->getDirectDial());

        $billingAddress = $customer->getAddresses()->getAddress('BILLING');
        self::assertNotNull($billingAddress);
        self::assertEquals('billing firstname', $billingAddress->getFirstName());
        self::assertEquals('billing lastname', $billingAddress->getLastName());
        self::assertEquals('billing street', $billingAddress->getStreet());
        self::assertEquals('12345', $billingAddress->getZipCode());
        self::assertEquals('billing city', $billingAddress->getCity());
        self::assertEquals('DE', $billingAddress->getCountryCode());
        self::assertNull($billingAddress->getCompany());

        $shippingAddress = $customer->getAddresses()->getAddress('DELIVERY');
        self::assertNotNull($shippingAddress);
        self::assertEquals('shipping firstname', $shippingAddress->getFirstName());
        self::assertEquals('shipping lastname', $shippingAddress->getLastName());
        self::assertEquals('shipping street', $shippingAddress->getStreet());
        self::assertEquals('12345', $shippingAddress->getZipCode());
        self::assertEquals('shipping city', $shippingAddress->getCity());
        self::assertEquals('DE', $shippingAddress->getCountryCode());
        self::assertNull($shippingAddress->getCompany());


        self::assertNull($customer->getBankAccount(), 'the whole bank account entity should be null.');

    }

    public function testGetDataDefaultPhoneNumber(): void
    {

        $requestData = $this->createRequestDataMock(
            'DE',
            'DE',
            true,
            false,
            false,
            false,
            false
        );

        $factory = $this->getContainer()->get(CustomerFactory::class);
        /** @var Customer $customer */
        $customer = $factory->getData($requestData);

        self::assertEquals('030', $customer->getContacts()->getPhone()->getAreaCode());
        self::assertEquals('33988560', $customer->getContacts()->getPhone()->getDirectDial());
    }

    public function testGetDataSameAddress(): void
    {

        $requestData = $this->createRequestDataMock(
            'DE',
            'DE',
            false,
            false,
            false,
            false,
            false
        );
        $factory = $this->getContainer()->get(CustomerFactory::class);

        /** @var Customer $customer */
        $customer = $factory->getData($requestData);

        $billingAddress = $customer->getAddresses()->getAddress('BILLING');
        $shippingAddress = $customer->getAddresses()->getAddress('DELIVERY');
        self::assertNotNull($billingAddress);
        self::assertNotNull($shippingAddress);

        $errorMessage = 'shipping address and billing address data should be the same';
        self::assertEquals($billingAddress->getFirstName(), $shippingAddress->getFirstName(), $errorMessage);
        self::assertEquals($billingAddress->getLastName(), $shippingAddress->getLastName(), $errorMessage);
        self::assertEquals($billingAddress->getStreet(), $shippingAddress->getStreet(), $errorMessage);
        self::assertEquals($billingAddress->getZipCode(), $shippingAddress->getZipCode(), $errorMessage);
        self::assertEquals($billingAddress->getCity(), $shippingAddress->getCity(), $errorMessage);
        self::assertEquals($billingAddress->getCountryCode(), $shippingAddress->getCountryCode(), $errorMessage);

    }

    public function testGetDataCompany(): void
    {
        $requestData = $this->createRequestDataMock(
            'DE',
            'DE',
            true,
            true,
            false,
            false,
            false
        );
        $factory = $this->getContainer()->get(CustomerFactory::class);

        /** @var Customer $customer */
        $customer = $factory->getData($requestData);
        self::assertEquals('billing company', $customer->getCompanyName());
        self::assertEquals('DE123456', $customer->getVatId());
        self::assertNull($customer->getDateOfBirth(), 'birthday should be null, cause the company is given');

        $billingAddress = $customer->getAddresses()->getAddress('BILLING');
        self::assertNotNull($billingAddress);
        self::assertEquals('billing company', $billingAddress->getCompany());

        $shippingAddress = $customer->getAddresses()->getAddress('DELIVERY');
        self::assertNotNull($shippingAddress);
        self::assertNull($shippingAddress->getCompany(), 'the company of the shipping address should be empty, cause the shipping address is a private address');
    }

    public function testGetBankData(): void
    {
        $requestData = $this->createRequestDataMock(
            'DE',
            'DE',
            true,
            true,
            false,
            false,
            true
        );
        $factory = $this->getContainer()->get(CustomerFactory::class);

        /** @var Customer $customer */
        $customer = $factory->getData($requestData);
        self::assertNotNull($customer->getBankAccount());
        self::assertEquals('DE02120300000000202051', $customer->getBankAccount()->getIban());
    }

    public function createRequestDataMock(
        string $billingCountry,
        string $shippingCountry,
        bool $differentAddresses,
        bool $hasCompanyBilling,
        bool $hasCompanyShipping,
        bool $hasPhoneNumber,
        bool $hasBankData
    ): PaymentRequestData
    {
        $order = new OrderEntity();
        $order->setId('e2b42780dfbd4fcc95e17f48ad29c871');

        // create language entity
        $locale = new LocaleEntity();
        $locale->setCode('de-DE');
        $language = new LanguageEntity();
        $language->setLocale($locale);
        $order->setLanguage($language);

        // create orderCustomer entity
        $orderCustomer = new OrderCustomerEntity();
        $orderCustomer->setRemoteAddress('123.456.789.123');
        $orderCustomer->setEmail('phpunit@dev.local');
        $orderCustomer->setCustomer(new CustomerEntity());
        $orderCustomer->getCustomer()->setBirthday((new DateTime())->setDate(1980, 12,30));
        $order->setOrderCustomer($orderCustomer);

        // create addresses
        $billingAddress = OrderAddressMock::createOrderBillingAddress($order, $billingCountry, $hasCompanyBilling, $hasPhoneNumber);
        $shippingAddress = $differentAddresses === false ? $billingAddress : OrderAddressMock::createOrderShippingAddress($order, $shippingCountry, $hasCompanyShipping, $hasPhoneNumber);
        $order->setAddresses(new OrderAddressCollection());
        $order->getAddresses()->set($billingAddress->getId(), $billingAddress);
        $order->setBillingAddressId($billingAddress->getId());

        $order->setDeliveries(new OrderDeliveryCollection());
        $orderDeliveryAddress = new OrderDeliveryEntity();
        $orderDeliveryAddress->setId('d6a797355bf64a7e877ac8a552346599');
        $orderDeliveryAddress->setShippingOrderAddress($shippingAddress);
        $orderDeliveryAddress->setShippingOrderAddressId($shippingAddress->getId());
        $order->getDeliveries()->set($orderDeliveryAddress->getId(), $orderDeliveryAddress);

        $salesChannelContextMock = $this->createMock(SalesChannelContext::class);
        $transaction = $this->createMock(OrderTransactionEntity::class);
        $profileConfig = $this->createMock(ProfileConfigEntity::class);

        $ratepayDataBag = new RequestDataBag();
        if($hasBankData) {
            $bankDataDataBag = new RequestDataBag([
               'iban' => 'DE02120300000000202051'
            ]);
            $ratepayDataBag->set('bankData', $bankDataDataBag);
        }
        return new PaymentRequestData($salesChannelContextMock, $order, $transaction, $profileConfig, new RequestDataBag(['ratepay' => $ratepayDataBag]));
    }

}
