<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\CheckoutRedirectFix\Helper;

use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;

class AddressHelper
{
    public static function createMd5Hash(CustomerAddressEntity $entity)
    {
        return md5(implode('', [
            $entity->getFirstName(),
            $entity->getLastName(),
            $entity->getStreet(),
            $entity->getZipcode(),
            $entity->getCity(),
            $entity->getCountryId(),
        ]));
    }
}
