<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Tests\Mock\Model;


use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\Salutation\SalutationEntity;

class AddressMock
{

    public const BILLING_ADDRESS_ID = '66d6bfb7aeb647dcbe397ad43f239d5c';
    public const SHIPPING_ADDRESS_ID = '028fd84f82984a43a2888da520df12ca';

    public static function createOrderBillingAddress(
        CustomerEntity $customer,
        string $countryCode,
        bool $isCompany = false,
        bool $hasPhoneNumber = true
    ): OrderAddressEntity
    {
        $address = static::createBillingAddress(...func_get_args());

        return OrderAddressEntity::createFrom(new ArrayStruct($address->getVars()));
    }

    public static function createOrderShippingAddress(
        CustomerEntity $customer,
        string $countryCode,
        bool $isCompany = false,
        bool $hasPhoneNumber = true
    ): CustomerAddressEntity
    {
        $address = static::createAddressEntity(
            $customer,
            self::SHIPPING_ADDRESS_ID,
            $isCompany,
            $countryCode,
            'shipping'
        );
        $address->setPhoneNumber($hasPhoneNumber ? '0987654321' : null);
        return $address;
    }

    public static function createBillingAddress(
        CustomerEntity $customer,
        string $countryCode,
        bool $hasCompany = false,
        bool $hasPhoneNumber = true
    ): CustomerAddressEntity
    {
        $address = static::createAddressEntity(
            $customer,
            self::BILLING_ADDRESS_ID,
            $hasCompany,
            $countryCode,
            'billing'
        );
        $address->setPhoneNumber($hasPhoneNumber ? '0123456789' : null);
        return $address;
    }

    public static function createShippingAddress(
        CustomerEntity $customer,
        string $countryCode,
        bool $hasCompany = false,
        bool $hasPhoneNumber = true
    ): CustomerAddressEntity
    {
        $address = static::createAddressEntity(
            $customer,
            self::SHIPPING_ADDRESS_ID,
            $hasCompany,
            $countryCode,
            'shipping'
        );
        $address->setPhoneNumber($hasPhoneNumber ? '0987654321' : null);
        return $address;
    }

    private static function createAddressEntity(
        CustomerEntity $customer,
        string $addressUuid,
        bool $hasCompany,
        string $countryCode,
        string $prefix
    ): CustomerAddressEntity
    {
        $address = new CustomerAddressEntity();
        $address->setId($addressUuid);
        $address->setCustomer($customer);
        $address->setCustomerId($customer->getId());
        if($hasCompany) {
            $address->setCompany($prefix . ' company');
            $address->setVatId('DE123456');
        }
        $address->setFirstName($prefix . ' firstname');
        $address->setLastName($prefix . ' lastname');
        $address->setStreet($prefix . ' street');
        $address->setZipcode("12345");
        $address->setCity($prefix . ' city');
        $address->setCountry(static::getCountry($countryCode));

        $salutation = new SalutationEntity();
        $salutation->setSalutationKey('mrs');
        $salutation->setDisplayName('Frau');
        $address->setSalutation($salutation);
        return $address;
    }

    private static function getCountry(string $countryCode): CountryEntity
    {
        $country = new CountryEntity();
        $country->setIso($countryCode);
        return $country;
    }

}
