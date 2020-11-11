<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Mock\RatepayApi\Factory;

use Ratepay\RpayPayments\Components\RatepayApi\Factory\CustomerFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ExternalFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\InvoiceFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\PaymentFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class Mock
{
    public static function createCustomerFactory(): CustomerFactory
    {
        return new CustomerFactory(new EventDispatcher(), new RequestStack());
    }

    public static function createExternalFactory(): ExternalFactory
    {
        return new ExternalFactory(new EventDispatcher(), new RequestStack());
    }

    public static function createHeadFactory(): HeadFactory
    {
        return new HeadFactory(new EventDispatcher(), new RequestStack(), '123', '456');
    }

    public static function createInvoiceFactory(): InvoiceFactory
    {
        return new InvoiceFactory(new EventDispatcher(), new RequestStack());
    }

    public static function createPaymentFactory(): PaymentFactory
    {
        return new PaymentFactory(new EventDispatcher(), new RequestStack());
    }

    public static function createShoppingBasketFactory(): ShoppingBasketFactory
    {
        return new ShoppingBasketFactory(new EventDispatcher(), new RequestStack());
    }
}
