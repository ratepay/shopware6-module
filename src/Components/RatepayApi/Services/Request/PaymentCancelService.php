<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


class PaymentCancelService extends AbstractModifyRequest
{
    protected $_subType = 'cancellation';
    protected $eventName = 'cancel';

    protected function updateCustomField(array &$customFields, $qty)
    {
        $customFields['canceled'] = $customFields['canceled'] + $qty;
    }

}
