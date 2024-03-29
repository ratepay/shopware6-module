<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Util;

use InvalidArgumentException;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class BankAccountHolderHelper
{
    /**
     * @return string[]
     */
    public static function getAvailableNames(OrderEntity|SalesChannelContext $baseData): array
    {
        if ($baseData instanceof SalesChannelContext) {
            $address = $baseData->getCustomer()->getDefaultBillingAddress();
        } elseif ($baseData instanceof OrderEntity) {
            $address = $baseData->getAddresses()->get($baseData->getBillingAddressId());
        }

        if (!isset($address)) {
            throw new InvalidArgumentException('`$baseData` must be one of ' . OrderEntity::class . ' or ' . SalesChannelContext::class);
        }

        $names = [];
        if (!empty($address->getCompany())) {
            $names[] = trim((string) $address->getCompany());
        }

        $names[] = trim((string) $address->getFirstName()) . ' ' . trim((string) $address->getLastName());

        return $names;
    }
}
