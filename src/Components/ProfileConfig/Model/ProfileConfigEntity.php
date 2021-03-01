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
    public const FIELD_ID = 'id';

    public const FIELD_PROFILE_ID = 'profileId';

    public const FIELD_SECURITY_CODE = 'securityCode';

    public const FIELD_SANDBOX = 'sandbox';

    public const FIELD_BACKEND = 'backend';

    public const FIELD_SALES_CHANNEL = 'salesChannel';

    public const FIELD_SALES_CHANNEL_ID = 'salesChannelId';

    public const FIELD_COUNTRY_CODE_BILLING = 'countryCodeBilling';

    public const FIELD_COUNTRY_CODE_SHIPPING = 'countryCodeDelivery';

    public const FIELD_CURRENCY = 'currency';

    public const FIELD_STATUS = 'status';

    public const FIELD_STATUS_MESSAGE = 'statusMessage';

    public const FIELD_PAYMENT_METHOD_CONFIGS = 'paymentMethodConfigs';

    use EntityIdTrait;

    /**
     * @var string
     */
    protected $profileId;

    /**
     * @var string
     */
    protected $securityCode;

    /**
     * @var SalesChannelEntity
     */
    protected $salesChannel;

    /**
     * @var int
     */
    protected $salesChannelId;

    /**
     * @var string[]
     */
    protected $countryCodeBilling;

    /**
     * @var string[]
     */
    protected $countryCodeDelivery;

    /**
     * @var string[]
     */
    protected $currency;

    /**
     * @var bool
     */
    protected $sandbox;

    /**
     * @var bool
     */
    protected $backend;

    /**
     * @var bool
     */
    protected $status;

    /**
     * @var string
     */
    protected $statusMessage;

    /**
     * @var ProfileConfigMethodCollection
     */
    protected $paymentMethodConfigs;

    /**
     * @return string[]
     */
    public function getCountryCodeBilling(): ?array
    {
        return $this->countryCodeBilling;
    }

    /**
     * @param string[] $countryCodeBilling
     */
    public function setCountryCodeBilling(array $countryCodeBilling = null): void
    {
        $this->countryCodeBilling = $countryCodeBilling;
    }

    /**
     * @return SalesChannelEntity
     */
    public function getSalesChannel(): ?SalesChannelEntity
    {
        return $this->salesChannel;
    }

    /**
     * @param SalesChannelEntity $salesChannel
     */
    public function setSalesChannel(SalesChannelEntity $salesChannel = null): void
    {
        $this->salesChannel = $salesChannel;
    }

    /**
     * @return string
     */
    public function getProfileId(): ?string
    {
        return $this->profileId;
    }

    /**
     * @param string $profileId
     */
    public function setProfileId(string $profileId = null): void
    {
        $this->profileId = $profileId;
    }

    /**
     * @return string
     */
    public function getSecurityCode(): ?string
    {
        return $this->securityCode;
    }

    /**
     * @param string $securityCode
     */
    public function setSecurityCode(string $securityCode = null): void
    {
        $this->securityCode = $securityCode;
    }

    /**
     * @return string[]
     */
    public function getCountryCodeDelivery(): ?array
    {
        return $this->countryCodeDelivery;
    }

    /**
     * @param string[] $countryCodeDelivery
     */
    public function setCountryCodeDelivery(array $countryCodeDelivery = null): void
    {
        $this->countryCodeDelivery = $countryCodeDelivery;
    }

    /**
     * @return string[]
     */
    public function getCurrency(): ?array
    {
        return $this->currency;
    }

    /**
     * @param string[] $currency
     */
    public function setCurrency(array $currency = null): void
    {
        $this->currency = $currency;
    }

    /**
     * @return bool
     */
    public function isSandbox(): ?bool
    {
        return $this->sandbox;
    }

    /**
     * @param bool $sandbox
     */
    public function setSandbox(bool $sandbox = null): void
    {
        $this->sandbox = $sandbox;
    }

    /**
     * @return bool
     */
    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatusMessage(): ?string
    {
        return $this->statusMessage;
    }

    public function setStatusMessage(string $statusMessage): void
    {
        $this->statusMessage = $statusMessage;
    }

    public function getSalesChannelId(): int
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(int $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    public function isBackend(): bool
    {
        return $this->backend;
    }

    public function setBackend(bool $backend): void
    {
        $this->backend = $backend;
    }

    public function setPaymentMethodConfigs(ProfileConfigMethodCollection $paymentMethodConfigs): void
    {
        $this->paymentMethodConfigs = $paymentMethodConfigs;
    }

    public function getPaymentMethodConfigs(): ProfileConfigMethodCollection
    {
        return $this->paymentMethodConfigs;
    }
}
