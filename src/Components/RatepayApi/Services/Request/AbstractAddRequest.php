<?php


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
