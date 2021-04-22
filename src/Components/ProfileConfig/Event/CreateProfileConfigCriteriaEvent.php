<?php

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
    /**
     * @var Criteria
     */
    private $criteria;

    /**
     * @var string
     */
    private $paymentMethodId;

    /**
     * @var string
     */
    private $billingCountryIso;

    /**
     * @var string
     */
    private $shippingCountryIso;

    /**
     * @var string
     */
    private $salesChannelId;

    /**
     * @var string
     */
    private $currencyIso;

    /**
     * @var bool
     */
    private $differentAddresses;

    /**
     * @var Context
     */
    private $context;

    public function __construct(
        Criteria $criteria,
        string $paymentMethodId,
        string $billingCountryIso,
        string $shippingCountryIso,
        string $salesChannelId,
        string $currencyIso,
        bool $differentAddresses,
        Context $context
    ) {
        $this->criteria = $criteria;
        $this->paymentMethodId = $paymentMethodId;
        $this->billingCountryIso = $billingCountryIso;
        $this->shippingCountryIso = $shippingCountryIso;
        $this->salesChannelId = $salesChannelId;
        $this->currencyIso = $currencyIso;
        $this->differentAddresses = $differentAddresses;
        $this->context = $context;
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

    public function getContext(): Context
    {
        return $this->context;
    }
}
