<?php

namespace Ratepay\RatepayPayments\Components\DeviceFingerprint;

use RatePAY\Service\DeviceFingerprint;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * ServiceClass for device fingerprinting
 * Class DfpService
 * @package Ratepay\Services
 */
class DfpService
{
    const SESSION_VAR_NAME = 'dfpToken';

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        RequestStack $requestStack
    )
    {
        $this->requestStack = $requestStack;
    }

    public function getDfpId($backend = false)
    {
        if ($backend === false) {
            // storefront request
            $sessionValue = $this->requestStack->getCurrentRequest()->getSession()->get(self::SESSION_VAR_NAME);
            if ($sessionValue && array_key_exists('0', $sessionValue)) {
                return $sessionValue[0];
            }

            if ($this->requestStack->getCurrentRequest()->getSession() !== null) {
                $sessionId = $this->requestStack->getCurrentRequest()->getSession()->get('sessionId');
            }

        } else {
            // admin or console request
            $sessionId = rand();
        }

        $token = DeviceFingerprint::createDeviceIdentToken($sessionId);

        if ($backend === false) {
            // if it is a storefront request we will safe the token to the session for later access
            // in the admin we only need it once
            

            // TODO add session handler einfügen
            $this->requestStack->getCurrentRequest()->setSession(self::SESSION_VAR_NAME, $token);
        }
        return $token;
    }

    public function isDfpIdAlreadyGenerated()
    {
        return $this->requestStack->getCurrentRequest()->getSession()->get(self::SESSION_VAR_NAME) !== null;
    }

}
