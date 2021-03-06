<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Util;

use RatePAY\Model\Response\AbstractResponse;
use RatePAY\Service\ValidateGatewayResponse;
use SimpleXMLElement;

class ResponseConverter
{
    public static function getResponseObjectByXml(string $operation, string $xml): AbstractResponse
    {
        $response = new ValidateGatewayResponse($operation, new SimpleXMLElement($xml));

        return $response->getResponseModel();
    }
}
