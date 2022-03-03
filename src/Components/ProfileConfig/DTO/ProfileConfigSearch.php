<?php


namespace Ratepay\RpayPayments\Components\ProfileConfig\Dto;


use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProfileConfigSearch
{

    private string $billingCountryId;

    private string $shippingCountryId;

    private string $billingCountryCode;

    private string $shippingCountryCode;

    private ?string $paymentMethodId;

    private bool $isAdminOrder;

    private SalesChannelContext $salesChannelContext;

    private string $currency;

    private bool $isB2b = false;

    private bool $needsAllowDifferentAddress = false;

    private float $totalAmount;

    public function getBillingCountryId(): string
    {
        return $this->billingCountryId;
    }

    public function setBillingCountryId(string $billingCountryId): self
    {
        $this->billingCountryId = $billingCountryId;

        return $this;
    }

    public function getShippingCountryId(): string
    {
        return $this->shippingCountryId;
    }

    public function setShippingCountryId(string $shippingCountryId): self
    {
        $this->shippingCountryId = $shippingCountryId;

        return $this;
    }

    public function getBillingCountryCode(): string
    {
        return $this->billingCountryCode;
    }

    public function setBillingCountryCode(string $billingCountryCode): self
    {
        $this->billingCountryCode = $billingCountryCode;

        return $this;
    }

    public function getShippingCountryCode(): string
    {
        return $this->shippingCountryCode;
    }

    public function setShippingCountryCode(string $shippingCountryCode): self
    {
        $this->shippingCountryCode = $shippingCountryCode;

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

    public function isAdminOrder(): bool
    {
        return $this->isAdminOrder;
    }

    public function setIsAdminOrder(bool $isAdminOrder): self
    {
        $this->isAdminOrder = $isAdminOrder;

        return $this;
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

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function isB2b(): bool
    {
        return $this->isB2b;
    }

    public function setIsB2b(bool $isB2b): self
    {
        $this->isB2b = $isB2b;

        return $this;
    }

    public function isNeedsAllowDifferentAddress(): bool
    {
        return $this->needsAllowDifferentAddress;
    }

    public function setNeedsAllowDifferentAddress(bool $needsAllowDifferentAddress): self
    {
        $this->needsAllowDifferentAddress = $needsAllowDifferentAddress;

        return $this;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(?float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;

        return $this;
    }
}
