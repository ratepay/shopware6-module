<?php

declare(strict_types=1);

namespace Ratepay\RpayPayments\Components\FeatureFlags\Util;

class FeatureFlagService
{

    private static array $flags = [];

    public static function isFlagEnabled(string $flag): bool
    {
        return in_array($flag, self::$flags);
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

        self::$flags = array_filter($list, static fn($value): bool => !empty($value));
    }
}
