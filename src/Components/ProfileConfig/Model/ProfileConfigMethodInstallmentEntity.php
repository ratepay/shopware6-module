<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Model;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ProfileConfigMethodInstallmentEntity extends Entity
{
    use EntityIdTrait;

    public const FIELD_ID = 'id';

    public const FIELD_ALLOWED_MONTHS = 'allowedMonths';

    public const FIELD_IS_BANKTRANSFER_ALLOWED = 'isBankTransferAllowed';

    public const FIELD_IS_DEBIT_ALLOWED = 'isDebitAllowed';

    public const FIELD_RATE_MIN = 'rateMin';

    /**
     * @var string
     */
    protected $allowedMonths;

    /**
     * @var bool
     */
    protected $isBankTransferAllowed;

    /**
     * @var bool
     */
    protected $isDebitAllowed;

    /**
     * @var int
     */
    protected $rateMin;

    public function getAllowedMonths(): string
    {
        return $this->allowedMonths;
    }

    public function setAllowedMonths(string $allowedMonths): void
    {
        $this->allowedMonths = $allowedMonths;
    }

    public function isBankTransferAllowed(): bool
    {
        return $this->isBankTransferAllowed;
    }

    public function setIsBankTransferAllowed(bool $isBankTransferAllowed): void
    {
        $this->isBankTransferAllowed = $isBankTransferAllowed;
    }

    public function isDebitAllowed(): bool
    {
        return $this->isDebitAllowed;
    }

    public function setIsDebitAllowed(bool $isDebitAllowed): void
    {
        $this->isDebitAllowed = $isDebitAllowed;
    }

    public function getRateMin(): int
    {
        return $this->rateMin;
    }

    public function setRateMin(int $rateMin): void
    {
        $this->rateMin = $rateMin;
    }
}
