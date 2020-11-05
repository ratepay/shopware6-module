<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Shopware\Core\Framework\Context;

class ProfileRequestData extends AbstractRequestData
{
    public function __construct(Context $context, ProfileConfigEntity $profileConfig)
    {
        parent::__construct($context);
        $this->setProfileConfig($profileConfig);
    }
}
