<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
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

    use EntityIdTrait;

    /**
     * @var PaymentMethodEntity
     */
    protected $paymentMethod;

    /**
     * @var string
     */
    protected $paymentMethodId;

    /**
     * @var ProfileConfigEntity
     */
    protected $profile;

    /**
     * @var string
     */
    protected $profileId;

    /**
     * @var int
     */
    protected $limitMin;

    /**
     * @var int
     */
    protected $limitMax;

    /**
     * @var int
     */
    protected $limitMaxB2b;

    /**
     * @var boolean
     */
    protected $allowDifferentAddresses;

    /**
     * @var boolean
     */
    protected $allowB2b;

    /**
     * @var ProfileConfigMethodInstallmentEntity
     */
    protected $installmentConfig;

    /**
     * @return string
     */
    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    /**
     * @param string $paymentMethod
     */
    public function setPaymentMethod(string $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return bool
     */
    public function isAllowB2b(): bool
    {
        return $this->allowB2b;
    }

    /**
     * @param bool $allowB2b
     */
    public function setAllowB2b(bool $allowB2b): void
    {
        $this->allowB2b = $allowB2b;
    }

    /**
     * @return int
     */
    public function getLimitMin(): int
    {
        return $this->limitMin;
    }

    /**
     * @param int $limitMin
     */
    public function setLimitMin(int $limitMin): void
    {
        $this->limitMin = $limitMin;
    }

    /**
     * @return int
     */
    public function getLimitMax(): int
    {
        return $this->limitMax;
    }

    /**
     * @param int $limitMax
     */
    public function setLimitMax(int $limitMax): void
    {
        $this->limitMax = $limitMax;
    }

    /**
     * @return int
     */
    public function getLimitMaxB2b(): ?int
    {
        return $this->limitMaxB2b;
    }

    /**
     * @param int $limitMaxB2b
     */
    public function setLimitMaxB2b(int $limitMaxB2b): void
    {
        $this->limitMaxB2b = $limitMaxB2b;
    }

    /**
     * @return bool
     */
    public function isAllowDifferentAddresses(): bool
    {
        return $this->allowDifferentAddresses;
    }

    /**
     * @param bool $allowDifferentAddresses
     */
    public function setAllowDifferentAddresses(bool $allowDifferentAddresses): void
    {
        $this->allowDifferentAddresses = $allowDifferentAddresses;
    }

    /**
     * @return ProfileConfigEntity
     */
    public function getProfile(): ProfileConfigEntity
    {
        return $this->profile;
    }

    /**
     * @param ProfileConfigEntity $profile
     */
    public function setProfile(ProfileConfigEntity $profile): void
    {
        $this->profile = $profile;
    }

    /**
     * @return string
     */
    public function getProfileId(): string
    {
        return $this->profileId;
    }

    /**
     * @param string $profileId
     */
    public function setProfileId(string $profileId): void
    {
        $this->profileId = $profileId;
    }

    /**
     * @return string
     */
    public function getPaymentMethodId(): string
    {
        return $this->paymentMethodId;
    }

    /**
     * @param string $paymentMethodId
     */
    public function setPaymentMethodId(string $paymentMethodId): void
    {
        $this->paymentMethodId = $paymentMethodId;
    }

    /**
     * @return ProfileConfigMethodInstallmentEntity|null
     */
    public function getInstallmentConfig(): ?ProfileConfigMethodInstallmentEntity
    {
        return $this->installmentConfig;
    }

    /**
     * @param ProfileConfigMethodInstallmentEntity|null $installmentConfig
     */
    public function setInstallmentConfig(?ProfileConfigMethodInstallmentEntity $installmentConfig): void
    {
        $this->installmentConfig = $installmentConfig;
    }

}
