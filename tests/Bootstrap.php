<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Ratepay\RpayPayments\Tests\TestConfig;
use Shopware\Core\TestBootstrapper;

require_once __DIR__ . '/../../../project/vendor/shopware/platform/src/Core/TestBootstrapper.php';
$bootstrapper = (new TestBootstrapper())
    ->setPlatformEmbedded(false)
    ->setLoadEnvFile(true)
    ->setForceInstallPlugins(true)
    ->addActivePlugins('RpayPayments')
    ->addCallingPlugin()
    ->bootstrap();

$bootstrapper->getClassLoader()->setPsr4('Ratepay\\RpayPayments\\Tests\\', __DIR__); // TODO composer

$configFile = __DIR__ . '/config.php';
TestConfig::initConfiguration($configFile);
