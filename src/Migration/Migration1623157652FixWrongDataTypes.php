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

class Migration1623157652FixWrongDataTypes extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1623157652;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `ratepay_profile_config_method` 
                CHANGE `limit_min` `limit_min` FLOAT(11) NULL DEFAULT NULL, 
                CHANGE `limit_max` `limit_max` FLOAT(11) NULL DEFAULT NULL, 
                CHANGE `limit_max_b2b` `limit_max_b2b` FLOAT(11) NULL DEFAULT NULL;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
