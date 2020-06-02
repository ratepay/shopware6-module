<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Core\ProfileConfig;


use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ProfileConfigMethodInstallmentEntity extends Entity
{

    const FIELD_ID = 'id';
    const FIELD_ALLOWED_MONTHS = 'allowedMonths';
    const FIELD_IS_BANKTRANSFER_ALLOWED = 'isBankTransferAllowed';
    const FIELD_IS_DEBIT_ALLOWED = 'isDebitAllowed';
    const FIELD_RATE_MIN = 'rateMin';

    use EntityIdTrait;

    /**
     * @var string
     */
    protected $allowedMonths;

    /**
     * @var boolean
     */
    protected $isBankTransferAllowed;

    /**
     * @var boolean
     */
    protected $isDebitAllowed;

    /**
     * @var int
     */
    protected $rateMin;

    /**
     * @return string
     */
    public function getAllowedMonths(): string
    {
        return $this->allowedMonths;
    }

    /**
     * @param string $allowedMonths
     */
    public function setAllowedMonths(string $allowedMonths): void
    {
        $this->allowedMonths = $allowedMonths;
    }

    /**
     * @return bool
     */
    public function isBankTransferAllowed(): bool
    {
        return $this->isBankTransferAllowed;
    }

    /**
     * @param bool $isBankTransferAllowed
     */
    public function setIsBankTransferAllowed(bool $isBankTransferAllowed): void
    {
        $this->isBankTransferAllowed = $isBankTransferAllowed;
    }

    /**
     * @return bool
     */
    public function isDebitAllowed(): bool
    {
        return $this->isDebitAllowed;
    }

    /**
     * @param bool $isDebitAllowed
     */
    public function setIsDebitAllowed(bool $isDebitAllowed): void
    {
        $this->isDebitAllowed = $isDebitAllowed;
    }

    /**
     * @return int
     */
    public function getRateMin(): int
    {
        return $this->rateMin;
    }

    /**
     * @param int $rateMin
     */
    public function setRateMin(int $rateMin): void
    {
        $this->rateMin = $rateMin;
    }
}
