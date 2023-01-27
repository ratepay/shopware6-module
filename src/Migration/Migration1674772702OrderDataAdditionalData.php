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

class Migration1674772702OrderDataAdditionalData extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1674772702;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `ratepay_order_data` ADD `additional_data` JSON NOT NULL AFTER `successful`;
        ');

        $connection->executeStatement('
            UPDATE ratepay_order_data SET additional_data = \'{}\'
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
