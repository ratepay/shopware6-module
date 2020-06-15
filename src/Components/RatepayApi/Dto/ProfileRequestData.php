<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Dto;


use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity;

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
