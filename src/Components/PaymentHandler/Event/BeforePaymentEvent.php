<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler\Event;

use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareEvent;
use Symfony\Contracts\EventDispatcher\Event;

class BeforePaymentEvent extends Event implements ShopwareEvent
{
    private PaymentRequestData $paymentRequestData;

    public function __construct(PaymentRequestData $paymentRequestData)
    {
        $this->paymentRequestData = $paymentRequestData;
    }

    public function getContext(): Context
    {
        return $this->paymentRequestData->getContext();
    }

    public function getPaymentRequestData(): PaymentRequestData
    {
        return $this->paymentRequestData;
    }
}
