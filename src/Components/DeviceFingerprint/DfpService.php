<?php

namespace Ratepay\RatepayPayments\Components\DeviceFingerprint;

use RatePAY\Service\DeviceFingerprint;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * ServiceClass for device fingerprinting
 * Class DfpService
 * @package Ratepay\Services
 */
class DfpService
{
    // TODO @aarends sollte noch irgendwas mit `ratepay` beinhalten - ála `ratepay_dfp_token`
    const SESSION_VAR_NAME = 'dfpToken';

    /*
     * @var SessionInterface
     */
    private $sessionInterface;

    public function __construct(
        SessionInterface $sessionInterface
    )
    {
        $this->sessionInterface = $sessionInterface;
    }

    // TODO @aarends das mit dem backend kannst du eigentlich komplett rausmachen. das brauchen wir nicht mehr.
    public function getDfpId($backend = false)
    {
        if ($backend === false) {
            // storefront request
            $sessionValue = $this->sessionInterface->get(self::SESSION_VAR_NAME);

            if ($sessionValue) {
                return $sessionValue;
            }

            $sessionId = $this->sessionInterface->get('sessionId');

        } else {
            // admin or console request
            $sessionId = rand();
        }
        $token = DeviceFingerprint::createDeviceIdentToken($sessionId);

        if ($backend === false) {
            // if it is a storefront request we will safe the token to the session for later access
            // in the admin we only need it once
            $this->sessionInterface->set(self::SESSION_VAR_NAME, $token);
        }
        return $token;
    }

    public function isDfpIdAlreadyGenerated()
    {
        return $this->sessionInterface->get(self::SESSION_VAR_NAME) !== null;
    }

}
