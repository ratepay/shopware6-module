<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Factory;


use RatePAY\Model\Request\SubModel\Head\External\Tracking;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\OrderOperationData;

class ExternalFactory extends AbstractFactory
{

    protected function _getData(IRequestData $requestData): ?object
    {
        /** @var OrderOperationData $requestData */

        $order = $requestData->getOrder();
        if ($delivery = $order->getDeliveries()->first()) {
            $tracking = new Tracking();
            $tracking->setId($delivery->getTrackingCodes()[0]);
            $supportedMethods = ['DHL', 'DPD', 'GLS', 'HLG', 'HVS', 'OTH', 'TNT', 'UPS'];
            foreach ($supportedMethods as $supportedMethod) {
                if (strpos($delivery->getShippingMethod()->getName(), $supportedMethod) === 0) {
                    $tracking->setProvider($supportedMethod);
                    break;
                }
            }
            return $tracking;
        }
        return null;
    }
}
