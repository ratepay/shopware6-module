<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity;

class ProfileRequestService extends AbstractRequest
{

    protected $_operation = self::CALL_PROFILE_REQUEST;

    /**
     * @var ProfileConfigEntity
     */
    protected $profileConfig = null;

    /**
     * @return ProfileConfigEntity
     */
    public function getProfileConfig()
    {
        return $this->profileConfig;
    }

    public function setProfileConfig(ProfileConfigEntity $profileConfig)
    {
        $this->profileConfig = $profileConfig;
    }
}
