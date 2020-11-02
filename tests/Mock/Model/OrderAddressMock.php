<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Tests\Mock\Model;


use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\Salutation\SalutationEntity;

class OrderAddressMock
{

    public const BILLING_ADDRESS_ID = '5a435abc21a14c0b94a3924356819cdb';
    public const SHIPPING_ADDRESS_ID = 'ec6ea9e298c74a6ea04dfe89a18cfefc';

    public static function createOrderShippingAddress(
        OrderEntity $order,
        string $countryCode,
        bool $hasCompany = false,
        bool $hasPhoneNumber = true
    ): OrderAddressEntity
    {
        $address = static::createAddressEntity(
            $order,
            self::SHIPPING_ADDRESS_ID,
            $hasCompany,
            $countryCode,
            'shipping'
        );
        $address->setPhoneNumber($hasPhoneNumber ? '0987654321' : null);
        return $address;
    }

    public static function createOrderBillingAddress(
        OrderEntity $order,
        string $countryCode,
        bool $hasCompany = false,
        bool $hasPhoneNumber = true
    ): OrderAddressEntity
    {
        $address = static::createAddressEntity(
            $order,
            self::BILLING_ADDRESS_ID,
            $hasCompany,
            $countryCode,
            'billing'
        );
        $address->setPhoneNumber($hasPhoneNumber ? '0123456789' : null);
        return $address;
    }

    private static function createAddressEntity(
        OrderEntity $order,
        string $addressUuid,
        bool $hasCompany,
        string $countryCode,
        string $prefix
    ): OrderAddressEntity
    {
        $address = new OrderAddressEntity();
        $address->setId($addressUuid);
        $address->setOrder($order);
        $address->setOrderId($order->getId());
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
