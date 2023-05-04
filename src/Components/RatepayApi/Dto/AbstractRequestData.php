<?php

declare(strict_types=1);

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
    protected ?ProfileConfigEntity $profileConfig = null;

    public function __construct(
        private readonly Context $context
    ) {
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
