<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Model;

use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileConfigSearch;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class InstallmentCalculatorContext
{
    /**
     * @var string
     */
    final public const CALCULATION_TYPE_TIME = 'time';

    /**
     * @var string
     */
    final public const CALCULATION_TYPE_RATE = 'rate';

    private float|int|null $calculationValue = null;

    private ?float $totalAmount = null;

    private bool $isUseCheapestRate = false;

    private ?OrderEntity $order = null;

    /**
     * @deprecated
     */
    private ?PaymentMethodEntity $paymentMethod = null;

    private ?string $paymentMethodId = null;

    private ?ProfileConfigSearch $profileConfigSearch = null;

    private ?CountryEntity $billingCountry = null;

    public function __construct(
        private SalesChannelContext $salesChannelContext,
        private string $calculationType,
        string|float|int|null $calculationValue = null
    ) {
        if ($this->calculationType === self::CALCULATION_TYPE_RATE) {
            $this->calculationValue = (float) $calculationValue;
        } elseif ($this->calculationType === self::CALCULATION_TYPE_TIME) {
            $this->calculationValue = (int) $calculationValue;
        }
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function setSalesChannelContext(SalesChannelContext $salesChannelContext): self
    {
        $this->salesChannelContext = $salesChannelContext;
        return $this;
    }

    public function getCalculationType(): string
    {
        return $this->calculationType;
    }

    public function setCalculationType(string $calculationType): self
    {
        $this->calculationType = $calculationType;
        return $this;
    }

    public function getCalculationValue(): float|int
    {
        return $this->calculationValue ?? 1;
    }

    public function setCalculationValue(float|int|null $calculationValue): self
    {
        $this->calculationValue = $calculationValue;
        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->order instanceof OrderEntity ? $this->order->getAmountTotal() : $this->totalAmount;
    }

    public function setTotalAmount(?float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(?OrderEntity $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getPaymentMethodId(): ?string
    {
        return $this->paymentMethodId;
    }

    public function setPaymentMethodId(?string $paymentMethodId): self
    {
        $this->paymentMethodId = $paymentMethodId;

        return $this;
    }

    /**
     * @deprecated please use getPaymentMethodId
     */
    public function getPaymentMethod(): ?PaymentMethodEntity
    {
        return $this->paymentMethod ?? $this->salesChannelContext->getPaymentMethod();
    }

    /**
     * @deprecated please use setPaymentMethodId
     */
    public function setPaymentMethod(?PaymentMethodEntity $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        $this->paymentMethodId = $paymentMethod->getId();

        return $this;
    }

    public function getLanguageId(): string
    {
        return $this->order instanceof OrderEntity ? $this->order->getLanguageId() : $this->salesChannelContext->getContext()->getLanguageId();
    }

    public function getBillingCountry(): CountryEntity
    {
        if ($this->billingCountry instanceof CountryEntity) {
            return $this->billingCountry;
        }

        if ($this->order instanceof OrderEntity) {
            return $this->order->getAddresses()->get($this->order->getBillingAddressId())->getCountry();
        }

        return $this->salesChannelContext->getCustomer()->getActiveBillingAddress()->getCountry();
    }

    public function isUseCheapestRate(): bool
    {
        return $this->isUseCheapestRate;
    }

    public function setIsUseCheapestRate(bool $isUseCheapestRate): self
    {
        $this->isUseCheapestRate = $isUseCheapestRate;

        return $this;
    }

    public function getProfileConfigSearch(): ?ProfileConfigSearch
    {
        return $this->profileConfigSearch;
    }

    public function setProfileConfigSearch(?ProfileConfigSearch $profileConfigSearch): self
    {
        $this->profileConfigSearch = $profileConfigSearch;

        return $this;
    }

    public function setBillingCountry(CountryEntity $billingCountry): self
    {
        $this->billingCountry = $billingCountry;

        return $this;
    }
}
