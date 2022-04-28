<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\ProfileConfig\Util;

use PHPUnit\Framework\TestCase;
use Ratepay\RpayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\PrepaymentPaymentHandler;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\Definition\ProfileConfigDefinition;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileBySalesChannelContext;
use Ratepay\RpayPayments\Components\ProfileConfig\Util\AddressUtil;
use Ratepay\RpayPayments\Tests\Mock\Model\AddressMock;
use Ratepay\RpayPayments\Tests\Mock\Model\CountryMock;
use Ratepay\RpayPayments\Tests\TestConfig;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressCollection;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\SalesChannelApiTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class AddressUtilTest extends TestCase
{
    public function testSameAddress()
    {
        $customerAddress = $this->createCustomerAddress('firstname lastname', '', 'street', 'DE');
        self::assertTrue(AddressUtil::areCustomerAddressObjectsIdentical($customerAddress, $customerAddress));
        $orderAddress = $this->createOrderAddress('firstname lastname', '', 'street', 'DE');
        self::assertTrue(AddressUtil::areOrderAddressObjectsIdentical($orderAddress, $orderAddress));

        $customerAddress1 = $this->createCustomerAddress('firstname lastname', '', 'street', 'DE');
        $customerAddress2 = $this->createCustomerAddress('firstname lastname', '', 'street', 'DE');
        $customerAddress2->setCountry($customerAddress1->getCountry());
        $customerAddress2->setCountryId($customerAddress2->getCountry()->getId());
        self::assertTrue(AddressUtil::areCustomerAddressObjectsIdentical($customerAddress1, $customerAddress2));

        $orderAddress1 = $this->createOrderAddress('firstname lastname', '', 'street', 'DE');
        $orderAddress2 = $this->createOrderAddress('firstname lastname', '', 'street', 'DE');
        $orderAddress2->setCountry($orderAddress1->getCountry());
        $orderAddress2->setCountryId($orderAddress2->getCountry()->getId());
        self::assertTrue(AddressUtil::areOrderAddressObjectsIdentical($orderAddress1, $orderAddress2));
    }

    public function testSameCompanyAddress()
    {
        $customerAddress = $this->createCustomerAddress('firstname lastname', 'My Company', 'street', 'DE');
        self::assertTrue(AddressUtil::areCustomerAddressObjectsIdentical($customerAddress, $customerAddress));
        $orderAddress = $this->createOrderAddress('firstname lastname', 'My Company', 'street', 'DE');
        self::assertTrue(AddressUtil::areOrderAddressObjectsIdentical($orderAddress, $orderAddress));

        $customerAddress1 = $this->createCustomerAddress('First-Employee John', 'My Company', 'street', 'DE');
        $customerAddress2 = $this->createCustomerAddress('Second-Employee Doe', 'My Company', 'street', 'DE');
        $customerAddress2->setCountry($customerAddress1->getCountry());
        $customerAddress2->setCountryId($customerAddress2->getCountry()->getId());
        self::assertTrue(AddressUtil::areCustomerAddressObjectsIdentical($customerAddress1, $customerAddress2));
    }

    public function testNotSameAddressName()
    {
        $customerAddress1 = $this->createCustomerAddress('firstname lastname', '', 'street', 'DE');
        $customerAddress2 = $this->createCustomerAddress('John Doe', '', 'street', 'DE');
        $customerAddress2->setCountry($customerAddress1->getCountry());
        $customerAddress2->setCountryId($customerAddress2->getCountry()->getId());
        self::assertFalse(AddressUtil::areCustomerAddressObjectsIdentical($customerAddress1, $customerAddress2));

        $orderAddress1 = $this->createOrderAddress('firstname lastname', '', 'street', 'DE');
        $orderAddress2 = $this->createOrderAddress('John Doe', '', 'street', 'DE');
        $orderAddress2->setCountry($orderAddress1->getCountry());
        $orderAddress2->setCountryId($orderAddress2->getCountry()->getId());
        self::assertFalse(AddressUtil::areOrderAddressObjectsIdentical($orderAddress1, $orderAddress2));
    }

    public function testNotSameAddressStreet()
    {
        $customerAddress1 = $this->createCustomerAddress('firstname lastname', '', 'street', 'DE');

        $customerAddress2 = $this->createCustomerAddress('firstname lastname', '', 'another street', 'DE');
        $customerAddress2->setCountry($customerAddress1->getCountry());
        $customerAddress2->setCountryId($customerAddress2->getCountry()->getId());
        self::assertFalse(AddressUtil::areCustomerAddressObjectsIdentical($customerAddress1, $customerAddress2));
    }

    private function createCustomerAddress($name, $company, $street, $countryCode): CustomerAddressEntity
    {
        $entity = new CustomerAddressEntity();
        $entity->setId(Uuid::randomHex());
        if (!empty($company)) {
            $entity->setCompany($company);
        }
        $entity->setFirstName(explode(' ', $name)[0]);
        $entity->setLastName(explode(' ', $name)[0]);
        $entity->setStreet($street);
        $entity->setCountry(CountryMock::createMock($countryCode));
        $entity->setCountryId($entity->getCountry()->getId());

        return $entity;
    }

    private function createOrderAddress($name, $company, $street, $countryCode): OrderAddressEntity
    {
        $entity = new OrderAddressEntity();
        $entity->setId(Uuid::randomHex());
        if (!empty($company)) {
            $entity->setCompany($company);
        }
        $entity->setFirstName(explode(' ', $name)[0]);
        $entity->setLastName(explode(' ', $name)[0]);
        $entity->setStreet($street);
        $entity->setCountry(CountryMock::createMock($countryCode));
        $entity->setCountryId($entity->getCountry()->getId());

        return $entity;
    }
}
