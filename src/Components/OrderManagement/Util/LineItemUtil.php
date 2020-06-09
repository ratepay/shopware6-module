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
        return [
            'id' => $lineItem->getId(),
            'name' => $lineItem->getLabel(),
            'ordered' => $lineItem->getQuantity(),
            'delivered' => $customFields['ratepay_delivered'],
            'canceled' => $customFields['ratepay_canceled'],
            'returned' => $customFields['ratepay_returned'],
            'maxDelivery' => $lineItem->getQuantity() - $customFields['ratepay_delivered'] - $customFields['ratepay_canceled'],
            'maxReturn' => $customFields['ratepay_delivered'] - $customFields['ratepay_returned']
        ];
    }

    public static function getShippingLineItem(OrderEntity $order)
    {
        if ($order->getShippingTotal() > 0) {
            $customFields = $order->getCustomFields();
            return [
                'id' => 'shipping',
                'name' => 'shipping',
                'ordered' => 1,
                'delivered' => $customFields['ratepay_shipping_delivered'],
                'canceled' => $customFields['ratepay_shipping_canceled'],
                'returned' => $customFields['ratepay_shipping_returned'],
                'maxDelivery' => $customFields['ratepay_shipping_delivered'] == 0 ? 1 : 0,
                'maxReturn' => ($customFields['ratepay_shipping_delivered'] - $customFields['ratepay_shipping_returned']) >= 1 ? 1 : 0
            ];
        }
        return null;
    }

}
