<?php

namespace Ratepay\RatepayPayments\Components\DeviceFingerprint;

use RatePAY\Service\DeviceFingerprint;
use Ratepay\RatepayPayments\Helper\Sessionhelper;

/**
 * ServiceClass for device fingerprinting
 * Class DfpService
 * @package Ratepay\Services
 */
class DfpService
{

    const SESSION_VAR_NAME = 'dfpToken';

    /**
     * @var SessionHelper
     */
    private $sessionHelper;

    public function __construct(SessionHelper $sessionHelper)
    {
        $this->sessionHelper = $sessionHelper;
    }

    public function getDfpId($backend = false)
    {
        if ($backend === false) {
            // storefront request
            $sessionValue = $this->sessionHelper->getData(self::SESSION_VAR_NAME);
            if ($sessionValue) {
                return $sessionValue;
            }
            $sessionId = $this->sessionHelper->getSession()->get('sessionId');
        } else {
            // admin or console request
            $sessionId = rand();
        }

        $token = DeviceFingerprint::createDeviceIdentToken($sessionId);

        if ($backend === false) {
            // if it is a storefront request we will safe the token to the session for later access
            // in the admin we only need it once
            $this->sessionHelper->setData(self::SESSION_VAR_NAME, $token);
        }
        return $token;
    }

    public function isDfpIdAlreadyGenerated()
    {
        return $this->sessionHelper->getData(self::SESSION_VAR_NAME) !== null;
    }

    public function deleteDfpId()
    {
        $this->sessionHelper->setData(self::SESSION_VAR_NAME, null);
    }

    protected function getSession()
    {
        return $this->sessionHelper->getSession();
    }

}
