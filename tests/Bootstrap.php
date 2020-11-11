<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Ratepay\RpayPayments\Tests\TestConfig;

require_once __DIR__ . '/../../../../vendor/shopware/platform/src/Core/TestBootstrap.php';
$loader->setPsr4('Ratepay\\RpayPayments\\Tests\\', __DIR__);                 // TODO composer

$configFile = __DIR__ . '/config.php';
TestConfig::initConfiguration($configFile);
