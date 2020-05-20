<?php


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
