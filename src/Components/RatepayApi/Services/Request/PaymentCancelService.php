<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;

class PaymentCancelService extends AbstractModifyRequest
{
    protected $_subType = 'cancellation';
    protected $eventName = 'cancel';

    protected function getLineItemsCustomFieldChanges(OrderLineItemEntity $lineItem, $qty)
    {
        return [
            'ratepay_canceled' => $lineItem->getCustomFields()['ratepay_canceled'] + $qty
        ];
    }

    protected function getShippingCustomFields($qty)
    {
        return [
            'ratepay_shipping_canceled' => $qty
        ];
    }

}
