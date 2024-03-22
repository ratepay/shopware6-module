<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Core;

class FeatureFlagService
{
    private static array $flags = [];

    public static function isFlagEnabled(string $flag): bool
    {
        return in_array($flag, self::$flags, true);
    }

    public static function getFlags(): array
    {
        return self::$flags;
    }

    public static function loadFeatureFlags(?string $flags = null): void
    {
        if ($flags === null) {
            self::$flags = [];

            return;
        }

        $list = str_replace(',', "\n", $flags);
        $list = explode("\n", $list);

        self::$flags = array_filter($list, static fn ($value): bool => !empty($value));
    }
}
