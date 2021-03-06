<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use RatePAY\Model\Request\SubModel\Head\External;
use RatePAY\Model\Request\SubModel\Head\External\Tracking;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;

/**
 * @method External getData(AbstractRequestData $requestData)
 */
class ExternalFactory extends AbstractFactory
{
    protected function isSupported(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof OrderOperationData;
    }

    protected function _getData(AbstractRequestData $requestData): ?object
    {
        /** @var OrderOperationData $requestData */
        $order = $requestData->getOrder();

        $external = new External();
        $external->setOrderId($order->getOrderNumber());

        if ($requestData->getOperation() === OrderOperationData::OPERATION_REQUEST) {
            $external->setMerchantConsumerId($order->getOrderCustomer()->getCustomerNumber());
        }

        if ($requestData->getOperation() === OrderOperationData::OPERATION_DELIVER) {
            $delivery = $order->getDeliveries()->first();
            if ($delivery) {
                $tracking = new Tracking();
                foreach ($delivery->getTrackingCodes() as $trackingCode) {
                    $id = new Tracking\Id();
                    $id->setId($trackingCode);
                    $supportedMethods = ['DHL', 'DPD', 'GLS', 'HLG', 'HVS', 'OTH', 'TNT', 'UPS'];
                    foreach ($supportedMethods as $supportedMethod) {
                        if (strpos($delivery->getShippingMethod()->getName(), $supportedMethod) === 0) {
                            $id->setProvider($supportedMethod);
                            break;
                        }
                    }
                    $tracking->addId($id);
                }
                if (count($tracking->getIds() ?: [])) {
                    $external->setTracking($tracking);
                }
            }
        }

        return $external;
    }
}
