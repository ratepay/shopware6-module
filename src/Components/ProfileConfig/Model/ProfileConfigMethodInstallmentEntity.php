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

    protected array $allowedMonths = [];

    protected ?bool $isBankTransferAllowed = null;

    protected ?bool $isDebitAllowed = null;

    protected int $rateMin;

    public function getAllowedMonths(): array
    {
        return $this->allowedMonths;
    }

    public function setAllowedMonths(array $allowedMonths): void
    {
        $this->allowedMonths = $allowedMonths;
    }

    public function getIsBankTransferAllowed(): ?bool
    {
        return $this->isBankTransferAllowed;
    }

    public function setIsBankTransferAllowed(?bool $isBankTransferAllowed): void
    {
        $this->isBankTransferAllowed = $isBankTransferAllowed;
    }

    public function getIsDebitAllowed(): ?bool
    {
        return $this->isDebitAllowed;
    }

    public function setIsDebitAllowed(?bool $isDebitAllowed): void
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
