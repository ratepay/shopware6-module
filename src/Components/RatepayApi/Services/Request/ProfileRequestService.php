<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


use Ratepay\RatepayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\ProfileRequestData;
use Shopware\Core\Framework\Context;

class ProfileRequestService extends AbstractRequest
{

    const EVENT_BUILD_HEAD = self::class . parent::EVENT_BUILD_HEAD;

    protected $_operation = self::CALL_PROFILE_REQUEST;


    protected function getProfileConfig(Context $context, IRequestData $requestData)
    {
        /** @var $requestData ProfileRequestData */
        return $requestData->getProfileConfig();
    }
}
