<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\Currency\CurrencyEntity;

interface OperationDataWithCart
{
    public function getCart(): Cart;

    public function getPaymentMethod(): PaymentMethodEntity;

    public function getCurrency(): CurrencyEntity;

    public function getTaxState(): string;
}
