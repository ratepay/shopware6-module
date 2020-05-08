<?php


namespace Ratepay\RatepayPayments\Core\ProfileConfig;


use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ProfileConfigMethodEntity extends Entity
{

    const FIELD_ID = 'id';
    const FIELD_PROFILE = 'profile';
    const FIELD_PROFILE_ID = 'profileId';
    const FIELD_PAYMENT_METHOD = 'paymentMethod';
    const FIELD_LIMIT_MIN = 'limitMin';
    const FIELD_LIMIT_MAX = 'limitMax';
    const FIELD_LIMIT_MAX_B2B = 'limitMaxB2b';
    const FIELD_ALLOW_DIFFERENT_ADDRESSES = 'allowDifferentAddresses';
    const FIELD_ALLOW_B2B = 'allowB2b';

    const PAYMENT_METHOD_PREPAYMENT = 'prepayment';
    const PAYMENT_METHOD_DEBIT = 'debit';
    const PAYMENT_METHOD_INVOICE = 'invoice';
    const PAYMENT_METHOD_INSTALLMENT = 'installment';
    const PAYMENT_METHOD_INSTALLMENT_0 = 'installment_zero_percent';

    const PAYMENT_METHODS = [
        self::PAYMENT_METHOD_PREPAYMENT,
        self::PAYMENT_METHOD_DEBIT,
        self::PAYMENT_METHOD_INVOICE,
        self::PAYMENT_METHOD_INSTALLMENT,
        self::PAYMENT_METHOD_INSTALLMENT_0
    ];

    use EntityIdTrait;

    /**
     * @var string
     */
    protected $paymentMethod;

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

}
