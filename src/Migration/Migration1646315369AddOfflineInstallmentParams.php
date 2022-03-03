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

class Migration1646315369AddOfflineInstallmentParams extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1646315369;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `ratepay_profile_config_method_installment`
                ADD `default_interest_rate` float NOT NULL,
                ADD `service_charge` float NOT NULL;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
