<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Model;

use BadMethodCallException;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;

class InstallmentBuilder extends \RatePAY\Frontend\InstallmentBuilder
{
    public function __construct(
        private readonly ProfileConfigEntity $profileConfig,
        private readonly ProfileConfigMethodEntity $methodConfig,
        string $language = 'DE',
        string $country = 'DE'
    ) {
        parent::__construct(
            $profileConfig->isSandbox(),
            $profileConfig->getProfileId(),
            $profileConfig->getSecurityCode(),
            $language,
            $country
        );
    }

    /**
     * @param string $profileId
     */
    public function setProfileId($profileId): void
    {
        if ($this->profileConfig->getProfileId() !== $profileId) {
            throw new BadMethodCallException('please do not set profile id manually. Please use constructor');
        }

        // call from constructor
        parent::setProfileId($profileId);
    }

    public function getProfileConfig(): ProfileConfigEntity
    {
        return $this->profileConfig;
    }

    public function getMethodConfig(): ProfileConfigMethodEntity
    {
        return $this->methodConfig;
    }
}
