<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Shopware\Core\Framework\Context;

class PaymentInitData extends AbstractRequestData
{
    public function __construct(ProfileConfigEntity $profileConfig, Context $context)
    {
        parent::__construct($context);
        $this->setProfileConfig($profileConfig);
    }
}
