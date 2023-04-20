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

    /**
     * @var string
     */
    public const FIELD_ID = 'id';

    /**
     * @var string
     */
    public const FIELD_PROFILE_ID = 'profileId';

    /**
     * @var string
     */
    public const FIELD_SECURITY_CODE = 'securityCode';

    /**
     * @var string
     */
    public const FIELD_SANDBOX = 'sandbox';

    /**
     * @var string
     */
    public const FIELD_ONLY_ADMIN_ORDERS = 'onlyAdminOrders';

    /**
     * @var string
     */
    public const FIELD_SALES_CHANNEL = 'salesChannel';

    /**
     * @var string
     */
    public const FIELD_SALES_CHANNEL_ID = 'salesChannelId';

    /**
     * @var string
     */
    public const FIELD_COUNTRY_CODE_BILLING = 'countryCodeBilling';

    /**
     * @var string
     */
    public const FIELD_COUNTRY_CODE_SHIPPING = 'countryCodeDelivery';

    /**
     * @var string
     */
    public const FIELD_CURRENCY = 'currency';

    /**
     * @var string
     */
    public const FIELD_STATUS = 'status';

    /**
     * @var string
     */
    public const FIELD_STATUS_MESSAGE = 'statusMessage';

    /**
     * @var string
     */
    public const FIELD_PAYMENT_METHOD_CONFIGS = 'paymentMethodConfigs';

    protected string $profileId;

    protected string $securityCode;

    protected ?SalesChannelEntity $salesChannel = null;

    protected string $salesChannelId;

    /**
     * @var string[]
     */
    protected array $countryCodeBilling = [];

    /**
     * @var string[]
     */
    protected array $countryCodeDelivery = [];

    /**
     * @var string[]
     */
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

    public function getSecurityCode(): string
    {
        return $this->securityCode;
    }

    public function getSalesChannel(): ?SalesChannelEntity
    {
        return $this->salesChannel;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    /**
     * @return string[]
     */
    public function getCountryCodeBilling(): array
    {
        return $this->countryCodeBilling;
    }

    /**
     * @return string[]
     */
    public function getCountryCodeDelivery(): array
    {
        return $this->countryCodeDelivery;
    }
    
    /**
     * @return string[]
     */
    public function getCurrency(): array
    {
        return $this->currency;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    public function isOnlyAdminOrders(): bool
    {
        return $this->onlyAdminOrders;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function getStatusMessage(): ?string
    {
        return $this->statusMessage;
    }

    public function getPaymentMethodConfigs(): ?ProfileConfigMethodCollection
    {
        return $this->paymentMethodConfigs;
    }
}
