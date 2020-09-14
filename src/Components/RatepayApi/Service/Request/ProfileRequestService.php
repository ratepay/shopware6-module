<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;


use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\ProfileRequestData;
use Shopware\Core\Framework\Context;

class ProfileRequestService extends AbstractRequest
{

    public const EVENT_BUILD_HEAD = self::class . parent::EVENT_BUILD_HEAD;

    protected $_operation = self::CALL_PROFILE_REQUEST;


    protected function getProfileConfig(Context $context, IRequestData $requestData): ProfileConfigEntity
    {
        /** @var $requestData ProfileRequestData */
        return $requestData->getProfileConfig();
    }
}
