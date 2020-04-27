<?php


namespace RatePay\RatePayPayments\Core\ProfileConfig;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class ProfileConfigEntity extends Entity
{

    use EntityIdTrait;

    /**
     * @var boolean
     */
    protected $zeroPercentInstallment;
    /**
     * @var string
     */
    protected $countryCodeBilling;
    /**
     * @var SalesChannelEntity
     */
    protected $salesChannel;
    /**
     * @var integer
     */
    protected $salesChannelId;
    /**
     * @var string
     */
    protected $profileId;
    /**
     * @var string
     */
    protected $securityCode;
    /**
     * @var string
     */
    protected $countryCodeDelivery;
    /**
     * @var string
     */
    protected $currency;
    /**
     * @var string
     */
    protected $errorDefault;
    /**
     * @var boolean
     */
    protected $sandbox;

    /**
     * @var boolean
     */
    protected $status;
    /**
     * @var string
     */
    protected $statusMessage;

    /**
     * @return bool
     */
    public function isZeroPercentInstallment(): ?bool
    {
        return $this->zeroPercentInstallment;
    }

    /**
     * @param bool $zeroPercentInstallment
     */
    public function setZeroPercentInstallment(bool $zeroPercentInstallment = null): void
    {
        $this->zeroPercentInstallment = $zeroPercentInstallment;
    }

    /**
     * @return string
     */
    public function getCountryCodeBilling(): ?string
    {
        return $this->countryCodeBilling;
    }

    /**
     * @param string $countryCodeBilling
     */
    public function setCountryCodeBilling(string $countryCodeBilling = null): void
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
     * @return string
     */
    public function getCountryCodeDelivery(): ?string
    {
        return $this->countryCodeDelivery;
    }

    /**
     * @param string $countryCodeDelivery
     */
    public function setCountryCodeDelivery(string $countryCodeDelivery = null): void
    {
        $this->countryCodeDelivery = $countryCodeDelivery;
    }

    /**
     * @return string
     */
    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency = null): void
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getErrorDefault(): ?string
    {
        return $this->errorDefault;
    }

    /**
     * @param string $errorDefault
     */
    public function setErrorDefault(string $errorDefault = null): void
    {
        $this->errorDefault = $errorDefault;
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

    /**
     * @param bool $status
     */
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

    /**
     * @param string $statusMessage
     */
    public function setStatusMessage(string $statusMessage): void
    {
        $this->statusMessage = $statusMessage;
    }

}
