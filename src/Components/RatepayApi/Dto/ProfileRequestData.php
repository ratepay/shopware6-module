<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;


use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;

class ProfileRequestData implements IRequestData
{

    /**
     * @var ProfileConfigEntity
     */
    private $profileConfig;

    public function __construct(ProfileConfigEntity $profileConfig)
    {
        $this->profileConfig = $profileConfig;
    }

    /**
     * @return ProfileConfigEntity
     */
    public function getProfileConfig(): ProfileConfigEntity
    {
        return $this->profileConfig;
    }
}
