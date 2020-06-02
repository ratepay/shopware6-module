<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


use RpayRatepay\Models\Position\Product;

abstract class AbstractAddRequest extends AbstractModifyRequest
{

    protected function getOrderPosition($basketPosition)
    {
        $position = new Product();
        $position->setOrderDetail($basketPosition->getOrderDetail());
        $this->modelManager->persist($position); //the model will be flushed in the caller
        return $position;
    }
}
