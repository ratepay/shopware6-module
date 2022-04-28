<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests;

use Exception;

class TestConfig
{
    public const MAPPING_DE = 'DE';

    public const MAPPING_AT = 'AT';

    public const MAPPING_CH = 'CH';

    public const MAPPING_DE_0RT = 'DE_0RT';

    public const MAPPING_AT_0RT = 'AT_0RT';

    /**
     * @var array
     */
    public static $configuration;

    public static function initConfiguration($configFile): void
    {
        if (false === file_exists($configFile)) {
            throw new Exception('please make sure, that the file ' . $configFile . ' does exists.');
        }

        /** @noinspection PhpIncludeInspection */
        $configuration = require $configFile;

        if (count($configuration) !== 5) {
            throw new Exception('Please make sure, that there are 5 profiles has been configured. Please have a look at ' . __DIR__ . '/config.php.dist');
        }

        self::$configuration = $configuration;
    }

    public static function getSecurityCode(string $isoCode, bool $isZeroPercent = false): ?string
    {
        return self::getConfiguration($isoCode, $isZeroPercent)['security_code'];
    }

    private static function getConfiguration(string $isoCode, bool $isZeroPercent = false): ?array
    {
        return self::$configuration[$isoCode . ($isZeroPercent ? '_0RT' : null)];
    }

    public static function getProfileId(string $isoCode, bool $isZeroPercent = false): ?string
    {
        return self::getConfiguration($isoCode, $isZeroPercent)['profile_id'];
    }

    public static function getAllUuids(bool $includeZeroPercent = true): array
    {
        $uuids = [];

        foreach ([self::MAPPING_DE, self::MAPPING_AT, self::MAPPING_CH] as $key) {
            $uuids[] = self::getUuid($key);
        }

        if ($includeZeroPercent) {
            foreach ([self::MAPPING_DE_0RT, self::MAPPING_AT_0RT] as $key) {
                $uuids[] = self::getUuid($key);
            }
        }

        return $uuids;
    }

    public static function getUuid(string $isoCode, bool $isZeroPercent = false): ?string
    {
        return self::getConfiguration($isoCode, $isZeroPercent)['uuid'];
    }
}
