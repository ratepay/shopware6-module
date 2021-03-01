<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Shopware\Core\Framework\Context;

abstract class AbstractRequestData
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ProfileConfigEntity|null
     */
    protected $profileConfig;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getProfileConfig(): ?ProfileConfigEntity
    {
        return $this->profileConfig;
    }

    public function setProfileConfig(?ProfileConfigEntity $profileConfig): self
    {
        $this->profileConfig = $profileConfig;

        return $this;
    }
}
