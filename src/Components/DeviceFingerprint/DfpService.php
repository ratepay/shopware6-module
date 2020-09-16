<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\DeviceFingerprint;

use RatePAY\Service\DeviceFingerprint;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DfpService
{
    const SESSION_VAR_NAME = 'ratepay_dfp_token';

    /**
     * @var SessionInterface
     */
    private $sessionInterface;

    public function __construct(SessionInterface $sessionInterface)
    {
        $this->sessionInterface = $sessionInterface;
    }

    public function getDfpId()
    {
        $sessionValue = $this->sessionInterface->get(self::SESSION_VAR_NAME);

        if ($sessionValue) {
            $token = $sessionValue;
        } else {
            $sessionId = $this->sessionInterface->get('sessionId');
            $token = DeviceFingerprint::createDeviceIdentToken($sessionId);
            $this->sessionInterface->set(self::SESSION_VAR_NAME, $token);
        }

        return $token;
    }

    public function isDfpIdAlreadyGenerated()
    {
        return $this->sessionInterface->get(self::SESSION_VAR_NAME) !== null;
    }

    public function deleteToken()
    {
        $this->sessionInterface->remove(self::SESSION_VAR_NAME);
    }
}
