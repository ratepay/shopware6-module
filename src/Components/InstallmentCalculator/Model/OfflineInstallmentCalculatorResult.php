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
    private InstallmentBuilder $builder;

    private InstallmentCalculatorContext $context;

    private float $monthCount;

    private float $monthlyRate;

    public function __construct(
        InstallmentCalculatorContext $context,
        InstallmentBuilder $builder,
        float $monthCount,
        float $monthlyRate
    ) {
        $this->context = $context;
        $this->builder = $builder;
        $this->monthCount = $monthCount;
        $this->monthlyRate = $monthlyRate;
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
