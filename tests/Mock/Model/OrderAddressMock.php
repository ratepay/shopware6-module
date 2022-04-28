<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Mock\Model;

use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\Salutation\SalutationEntity;

class OrderAddressMock
{
    public const BILLING_ADDRESS_ID = '5a435abc21a14c0b94a3924356819cdb';

    public const SHIPPING_ADDRESS_ID = 'ec6ea9e298c74a6ea04dfe89a18cfefc';

    public static function createAddressEntity(
        OrderEntity $order,
        bool $hasCompany,
        CountryEntity $country
    ): OrderAddressEntity
    {
        $address = new OrderAddressEntity();
        $address->setId(Uuid::randomHex());
        $address->setOrder($order);
        $address->setOrderId($order->getId());
        if ($hasCompany) {
            $address->setCompany('company');
            $address->setVatId('DE123456');
        }
        $address->setFirstName('firstname');
        $address->setLastName('lastname');
        $address->setStreet('street');
        $address->setZipcode('12345');
        $address->setCity('city');
        $address->setCountry($country);

        $salutation = new SalutationEntity();
        $salutation->setSalutationKey('mrs');
        $salutation->setDisplayName('Frau');
        $address->setSalutation($salutation);

        return $address;
    }
}
