<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Event;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class CreateProfileConfigCriteriaEvent
{
    public function __construct(
        private readonly Criteria $criteria,
        private readonly string $paymentMethodId,
        private readonly string $billingCountryIso,
        private readonly string $shippingCountryIso,
        private readonly string $salesChannelId,
        private readonly string $currencyIso,
        private readonly bool $differentAddresses,
        private readonly bool $isB2b,
        private readonly float $totalAmount,
        private readonly Context $context
    ) {
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    public function getPaymentMethodId(): string
    {
        return $this->paymentMethodId;
    }

    public function getBillingCountryIso(): string
    {
        return $this->billingCountryIso;
    }

    public function getShippingCountryIso(): string
    {
        return $this->shippingCountryIso;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    public function getCurrencyIso(): string
    {
        return $this->currencyIso;
    }

    public function isDifferentAddresses(): bool
    {
        return $this->differentAddresses;
    }

    public function isB2b(): bool
    {
        return $this->isB2b;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
