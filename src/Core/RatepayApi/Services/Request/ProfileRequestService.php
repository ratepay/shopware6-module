<?php


namespace Ratepay\RatepayPayments\Core\RatepayApi\Services\Request;


use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity;

class ProfileRequestService extends AbstractRequest
{
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

    /**
     * @return string
     */
    protected function getCallName()
    {
        return self::CALL_PROFILE_REQUEST;
    }

    /**
     * @return array
     */
    protected function getRequestContent()
    {
        return [];
    }

    protected function processSuccess()
    {
    }
}
