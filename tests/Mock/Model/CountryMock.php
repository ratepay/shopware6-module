<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Mock\Model;

use Ratepay\RpayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\PrepaymentPaymentHandler;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Country\CountryEntity;

class CountryMock
{

    public static function createMock($countryCode): CountryEntity
    {
        $method = new CountryEntity();
        $method->setId(Uuid::randomHex());
        $method->setIso($countryCode);

        return $method;
    }
}
