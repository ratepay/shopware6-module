<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\OrderManagement\Util;


use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;

class LineItemUtil
{

    public static function getLineItemArray(OrderLineItemEntity $lineItem)
    {
        $customFields = $lineItem->getCustomFields();
        $customFields['ratepay'] = $customFields['ratepay'] ?? self::getEmptyCustomFields();
        $data = [
            'id' => $lineItem->getId(),
            'name' => $lineItem->getLabel(),
            'ordered' => $lineItem->getQuantity(),
            'delivered' => $customFields['ratepay']['delivered'],
            'canceled' => $customFields['ratepay']['canceled'],
            'returned' => $customFields['ratepay']['returned'],
        ];
        return self::addMaxActionValues($data);
    }

    public static function getShippingLineItem(OrderEntity $order)
    {
        if ($order->getShippingTotal() > 0) {
            $customFields = $order->getCustomFields();
            $customFields['ratepay'] = $customFields['ratepay'] ?? [];
            $customFields['ratepay']['shipping'] = $customFields['ratepay']['shipping'] ?? self::getEmptyCustomFields();
            $data = [
                'id' => 'shipping',
                'name' => 'shipping',
                'ordered' => 1,
                'delivered' => $customFields['ratepay']['shipping']['delivered'] ?? 0,
                'canceled' => $customFields['ratepay']['shipping']['canceled'] ?? 0,
                'returned' => $customFields['ratepay']['shipping']['returned'] ?? 0,
            ];
            return self::addMaxActionValues($data);
        }
        return null;
    }

    protected static function addMaxActionValues(array $position)
    {
        $position['maxDelivery'] = $position['ordered'] - ($position['delivered']) - ($position['canceled']);
        $position['maxCancel'] = $position['ordered'] - ($position['delivered']) - ($position['canceled']);
        $position['maxReturn'] = ($position['delivered']) - ($position['returned']);
        return $position;
    }

    public static function getEmptyCustomFields()
    {
        return [
            'delivered' => 0,
            'canceled' => 0,
            'returned' => 0
        ];
    }

}
