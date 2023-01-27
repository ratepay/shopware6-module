<?php

namespace Ratepay\RpayPayments\Components\ProfileConfig\Util;

use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class AddressUtil
{

    public static function areOrderAddressObjectsIdentical(OrderAddressEntity $entity1, OrderAddressEntity $entity2): bool
    {
        $fieldsToCompare = [
            'company',
            'street',
            'additionalAddressLine1',
            'additionalAddressLine2',
            'zipcode',
            'city',
            'countryId',
            'countryStateId'
        ];

        if (empty($entity1->getCompany()) || empty($entity2->getCompany())) {
            $fieldsToCompare = array_merge($fieldsToCompare, ['firstName', 'lastName', 'salutationId']);
        }

        return self::areEntitiesIdentical($entity1, $entity2, $fieldsToCompare);
    }

    public static function areCustomerAddressObjectsIdentical(CustomerAddressEntity $entity1, CustomerAddressEntity $entity2): bool
    {
        $fieldsToCompare = [
            'company',
            'street',
            'additionalAddressLine1',
            'additionalAddressLine2',
            'zipcode',
            'city',
            'countryId',
            'countryStateId'
        ];

        if (empty($entity1->getCompany()) || empty($entity2->getCompany())) {
            $fieldsToCompare = array_merge($fieldsToCompare, ['firstName', 'lastName', 'salutationId']);
        }

        return self::areEntitiesIdentical($entity1, $entity2, $fieldsToCompare);
    }

    private static function areEntitiesIdentical(Entity $entity1, Entity $entity2, array $fields): bool
    {
        foreach ($fields as $field) {
            if ($entity1->get($field) !== $entity2->get($field)) {
                return false;
            }
        }

        return true;
    }

}