<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Mock\Model;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\Collection\ProfileConfigMethodCollection;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;

trait ProfileConfigMock
{

    public function createDefaultConfig()
    {
        $entity = new ProfileConfigEntity();
        $entity->setId('c0b3b9483cc8e01b4ba187877374e9f7');
        $entity->setBackend(false);
        $entity->setProfileId('SHOPWARE6_TE_DE');
        $entity->setSecurityCode('2LCMMNedklSJOahO5bvpjzpbuY9mIV0w');
        $entity->setCountryCodeBilling('DE');
        $entity->setCountryCodeDelivery('DE');

        $invoice = new ProfileConfigMethodEntity();
        $invoice->setAllowB2b(true);
        $invoice->setLimitMin(1);
        $invoice->setLimitMax(8000);
        $invoice->setLimitMaxB2b(5000000);
        $invoice->setAllowDifferentAddresses(true);
        //$invoice->setPaym

        $entity->setPaymentMethodConfigs(new ProfileConfigMethodCollection([
        ]));
        $entity->setStatus(true);

        return $entity;
    }
}
