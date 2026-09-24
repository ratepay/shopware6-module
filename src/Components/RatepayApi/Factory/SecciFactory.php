<?php declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use RatePAY\Model\Request\SubModel\Content\Secci;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\SecciRequestData;

/**
 * @extends AbstractFactory<Secci>
 */
class SecciFactory extends AbstractFactory
{

    protected function isSupported(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof SecciRequestData;
    }

    protected function _getData(AbstractRequestData $requestData): ?object
    {
        /** @var SecciRequestData $requestData */

        $secci = new Secci();
        $secci->setDeliveryMethod($requestData->getDeliveryMethod());
        $secci->setLanguage($requestData->getLanguageIso());
        $secci->setCountryCode($requestData->getCountryCode());

        if ($requestData->getDeliveryMethod() === SecciRequestData::DELIVERY_METHOD_EMAIL) {
            $secci->setEmail($requestData->getEmail());
        } else {
            $secci->setAction(SecciRequestData::ACTION_DOWNLOAD);
        }

        return $secci;
    }
}