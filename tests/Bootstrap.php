<?php

declare(strict_types=1);

use Ratepay\RpayPayments\Tests\TestConfig;

require_once __DIR__.'/../../../../vendor/shopware/platform/src/Core/TestBootstrap.php';
$loader->setPsr4("Ratepay\\RpayPayments\\Tests\\", __DIR__);                 // TODO composer

$configFile = __DIR__ . '/config.php';
TestConfig::initConfiguration($configFile);
