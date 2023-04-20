<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;

use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;

interface OperationDataWithBasket
{
    public function getItems(): array;

    public function getCurrencyCode(): string;

    public function getShippingCosts(): ?CalculatedPrice;

    public function isSendDiscountAsCartItem(): bool;

    public function isSendShippingCostsAsCartItem(): bool;
}
