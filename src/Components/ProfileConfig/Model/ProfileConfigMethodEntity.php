<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Model;

use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ProfileConfigMethodEntity extends Entity
{
    use EntityIdTrait;

    public const FIELD_ID = 'id';

    public const FIELD_PROFILE = 'profile';

    public const FIELD_PROFILE_ID = 'profileId';

    public const FIELD_PAYMENT_METHOD = 'paymentMethod';

    public const FIELD_PAYMENT_METHOD_ID = 'paymentMethodId';

    public const FIELD_LIMIT_MIN = 'limitMin';

    public const FIELD_LIMIT_MAX = 'limitMax';

    public const FIELD_LIMIT_MAX_B2B = 'limitMaxB2b';

    public const FIELD_ALLOW_DIFFERENT_ADDRESSES = 'allowDifferentAddresses';

    public const FIELD_ALLOW_B2B = 'allowB2b';

    public const FIELD_INSTALLMENT_CONFIG = 'installmentConfig';

    protected ?PaymentMethodEntity $paymentMethod = null;

    protected string $paymentMethodId;

    protected ?ProfileConfigEntity $profile = null;

    protected string $profileId;

    protected ?float $limitMin;

    protected ?float $limitMax;

    protected ?float $limitMaxB2b;

    protected bool $allowDifferentAddresses;

    protected ?bool $allowB2b = null;

    protected ?ProfileConfigMethodInstallmentEntity $installmentConfig = null;

    public function getPaymentMethod(): ?PaymentMethodEntity
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?PaymentMethodEntity $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function getPaymentMethodId(): string
    {
        return $this->paymentMethodId;
    }

    public function setPaymentMethodId(string $paymentMethodId): void
    {
        $this->paymentMethodId = $paymentMethodId;
    }

    public function getProfile(): ProfileConfigEntity
    {
        return $this->profile;
    }

    public function setProfile(ProfileConfigEntity $profile): void
    {
        $this->profile = $profile;
    }

    public function getProfileId(): string
    {
        return $this->profileId;
    }

    public function setProfileId(string $profileId): void
    {
        $this->profileId = $profileId;
    }

    public function getLimitMin(): ?float
    {
        return $this->limitMin;
    }

    public function setLimitMin(?float $limitMin): void
    {
        $this->limitMin = $limitMin;
    }

    public function getLimitMax(): ?float
    {
        return $this->limitMax;
    }

    public function setLimitMax(?float $limitMax): void
    {
        $this->limitMax = $limitMax;
    }

    public function getLimitMaxB2b(): ?float
    {
        return $this->limitMaxB2b;
    }

    public function setLimitMaxB2b(?float $limitMaxB2b): void
    {
        $this->limitMaxB2b = $limitMaxB2b;
    }

    public function isAllowDifferentAddresses(): bool
    {
        return $this->allowDifferentAddresses;
    }

    public function setAllowDifferentAddresses(bool $allowDifferentAddresses): void
    {
        $this->allowDifferentAddresses = $allowDifferentAddresses;
    }

    public function isAllowB2b(): ?bool
    {
        return $this->allowB2b;
    }

    public function setAllowB2b(?bool $allowB2b): void
    {
        $this->allowB2b = $allowB2b;
    }

    public function getInstallmentConfig(): ?ProfileConfigMethodInstallmentEntity
    {
        return $this->installmentConfig;
    }

    public function setInstallmentConfig(?ProfileConfigMethodInstallmentEntity $installmentConfig): void
    {
        $this->installmentConfig = $installmentConfig;
    }
}
