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

    protected float $rateMin;

    public function getAllowedMonths(): array
    {
        return $this->allowedMonths;
    }

    public function getIsBankTransferAllowed(): ?bool
    {
        return $this->isBankTransferAllowed;
    }

    public function getIsDebitAllowed(): ?bool
    {
        return $this->isDebitAllowed;
    }

    public function getRateMin(): float
    {
        return $this->rateMin;
    }
}
