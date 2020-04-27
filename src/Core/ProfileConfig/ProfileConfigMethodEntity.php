<?php


namespace Ratepay\RatepayPayments\Core\ProfileConfig;


use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class ProfileConfigMethodEntity extends Entity
{

    /**
     * @var boolean
     */
    protected $allowB2b;

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
}
