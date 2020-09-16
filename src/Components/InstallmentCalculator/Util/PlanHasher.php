<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Util;

class PlanHasher
{
    public static function isPlanEqualWithHash(string $hash, array $planData): bool
    {
        return self::hashPlan($planData) === $hash;
    }

    public static function hashPlan(array $planData): string
    {
        return md5(serialize($planData));
    }
}
