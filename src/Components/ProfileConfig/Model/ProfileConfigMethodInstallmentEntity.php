<?php

declare(strict_types=1);

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

    /**
     * @var string
     */
    final public const PAYMENT_TYPE_DIRECT_DEBIT = 'DIRECT-DEBIT';

    /**
     * @var string
     */
    final public const PAYMENT_TYPE_BANK_TRANSFER = 'BANK-TRANSFER';

    /**
     * @var string
     */
    final public const FIELD_ID = 'id';

    /**
     * @var string
     */
    final public const FIELD_ALLOWED_MONTHS = 'allowedMonths';

    /**
     * @var string
     */
    final public const FIELD_IS_BANKTRANSFER_ALLOWED = 'isBankTransferAllowed';

    /**
     * @var string
     */
    final public const FIELD_IS_DEBIT_ALLOWED = 'isDebitAllowed';

    /**
     * @var string
     */
    final public const FIELD_RATE_MIN = 'rateMin';

    /**
     * @var string
     */
    final public const FIELD_DEFAULT_PAYMENT_TYPE = 'defaultPaymentType';

    /**
     * @var string
     */
    final public const FIELD_DEFAULT_INTEREST_RATE = 'defaultInterestRate';

    /**
     * @var string
     */
    final public const FIELD_SERVICE_CHARGE = 'serviceCharge';

    /**
     * @var int[]
     */
    protected array $allowedMonths = [];

    protected ?bool $isBankTransferAllowed = null;

    protected ?bool $isDebitAllowed = null;

    protected float $rateMin;

    protected string $defaultPaymentType;

    protected float $defaultInterestRate;

    protected float $serviceCharge;

    /**
     * @return int[]
     */
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

    public function getDefaultPaymentType(): string
    {
        return $this->defaultPaymentType;
    }

    public function getDefaultInterestRate(): float
    {
        return $this->defaultInterestRate;
    }

    public function getServiceCharge(): float
    {
        return $this->serviceCharge;
    }
}
