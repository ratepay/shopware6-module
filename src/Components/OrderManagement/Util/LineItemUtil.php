<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\OrderManagement\Util;


use Ratepay\RatepayPayments\Components\Checkout\Model\RatepayPositionEntity;

class LineItemUtil
{

    public static function addMaxActionValues(RatepayPositionEntity $position, int $ordered): array
    {
        $return = $position->getVars();
        $return['maxDelivery'] = $ordered - ($position->getDelivered()) - ($position->getCanceled());
        $return['maxCancel'] = $ordered - ($position->getDelivered()) - ($position->getCanceled());
        $return['maxReturn'] = ($position->getDelivered()) - ($position->getReturned());
        return $return;
    }

}
