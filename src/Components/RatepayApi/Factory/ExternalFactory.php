<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use RatePAY\Model\Request\SubModel\Head\External;
use RatePAY\Model\Request\SubModel\Head\External\Tracking;
use RatePAY\Model\Request\SubModel\Head\External\Tracking\Id;
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
            if ($delivery instanceof OrderDeliveryEntity) {
                $tracking = new Tracking();
                foreach ($delivery->getTrackingCodes() as $trackingCode) {
                    $id = new Id();
                    $id->setId($trackingCode);
                    $id->setProvider('OTH');
                    $supportedMethods = ['DHL', 'DPD', 'GLS', 'HLG', 'HVS', 'OTH', 'TNT', 'UPS'];
                    foreach ($supportedMethods as $supportedMethod) {
                        if (str_starts_with((string) $delivery->getShippingMethod()->getName(), $supportedMethod)) {
                            $id->setProvider($supportedMethod);
                            break;
                        }
                    }

                    $tracking->addId($id);
                }

                if (($tracking->getIds() ?: []) !== []) {
                    $external->setTracking($tracking);
                }
            }
        }

        return $external;
    }
}
