<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Model;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\Collection\ProfileConfigMethodCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class ProfileConfigEntity extends Entity
{
    use EntityIdTrait;

    public const FIELD_ID = 'id';

    public const FIELD_PROFILE_ID = 'profileId';

    public const FIELD_SECURITY_CODE = 'securityCode';

    public const FIELD_SANDBOX = 'sandbox';

    public const FIELD_ONLY_ADMIN_ORDERS = 'onlyAdminOrders';

    public const FIELD_SALES_CHANNEL = 'salesChannel';

    public const FIELD_SALES_CHANNEL_ID = 'salesChannelId';

    public const FIELD_COUNTRY_CODE_BILLING = 'countryCodeBilling';

    public const FIELD_COUNTRY_CODE_SHIPPING = 'countryCodeDelivery';

    public const FIELD_CURRENCY = 'currency';

    public const FIELD_STATUS = 'status';

    public const FIELD_STATUS_MESSAGE = 'statusMessage';

    public const FIELD_PAYMENT_METHOD_CONFIGS = 'paymentMethodConfigs';

    protected string $profileId;

    protected string $securityCode;

    protected ?SalesChannelEntity $salesChannel = null;

    protected string $salesChannelId;

    protected array $countryCodeBilling = [];

    protected array $countryCodeDelivery = [];

    protected array $currency = [];

    protected bool $sandbox;

    protected bool $onlyAdminOrders;

    protected ?bool $status = null;

    protected ?string $statusMessage = null;

    protected ?ProfileConfigMethodCollection $paymentMethodConfigs = null;

    public function getProfileId(): string
    {
        return $this->profileId;
    }

    public function setProfileId(string $profileId): void
    {
        $this->profileId = $profileId;
    }

    public function getSecurityCode(): string
    {
        return $this->securityCode;
    }

    public function setSecurityCode(string $securityCode): void
    {
        $this->securityCode = $securityCode;
    }

    public function getSalesChannel(): ?SalesChannelEntity
    {
        return $this->salesChannel;
    }

    public function setSalesChannel(?SalesChannelEntity $salesChannel): void
    {
        $this->salesChannel = $salesChannel;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    public function getCountryCodeBilling(): array
    {
        return $this->countryCodeBilling;
    }

    public function setCountryCodeBilling(array $countryCodeBilling): void
    {
        $this->countryCodeBilling = $countryCodeBilling;
    }

    public function getCountryCodeDelivery(): array
    {
        return $this->countryCodeDelivery;
    }

    public function setCountryCodeDelivery(array $countryCodeDelivery): void
    {
        $this->countryCodeDelivery = $countryCodeDelivery;
    }

    public function getCurrency(): array
    {
        return $this->currency;
    }

    public function setCurrency(array $currency): void
    {
        $this->currency = $currency;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    public function setSandbox(bool $sandbox): void
    {
        $this->sandbox = $sandbox;
    }

    public function isOnlyAdminOrders(): bool
    {
        return $this->onlyAdminOrders;
    }

    public function setOnlyAdminOrders(bool $onlyAdminOrders): void
    {
        $this->onlyAdminOrders = $onlyAdminOrders;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): void
    {
        $this->status = $status;
    }

    public function getStatusMessage(): ?string
    {
        return $this->statusMessage;
    }

    public function setStatusMessage(?string $statusMessage): void
    {
        $this->statusMessage = $statusMessage;
    }

    public function getPaymentMethodConfigs(): ?ProfileConfigMethodCollection
    {
        return $this->paymentMethodConfigs;
    }

    public function setPaymentMethodConfigs(?ProfileConfigMethodCollection $paymentMethodConfigs): void
    {
        $this->paymentMethodConfigs = $paymentMethodConfigs;
    }
}
