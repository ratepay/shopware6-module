<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Model;

class OfflineInstallmentCalculatorResult
{
    public function __construct(
        private readonly InstallmentCalculatorContext $context,
        private readonly InstallmentBuilder $builder,
        private readonly float $monthCount,
        private readonly float $monthlyRate
    ) {
    }

    public function getContext(): InstallmentCalculatorContext
    {
        return $this->context;
    }

    public function getBuilder(): InstallmentBuilder
    {
        return $this->builder;
    }

    public function getMonthCount(): float
    {
        return $this->monthCount;
    }

    public function getMonthlyRate(): float
    {
        return $this->monthlyRate;
    }
}
