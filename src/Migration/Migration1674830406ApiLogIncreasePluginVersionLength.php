<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1674830406ApiLogIncreasePluginVersionLength extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1674830406;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `ratepay_api_log` CHANGE `version` `version` VARCHAR(20) NOT NULL;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
