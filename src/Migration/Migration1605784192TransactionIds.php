<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1605784192TransactionIds extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1605784192;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE `ratepay_transaction_id_temp` (
                `id` binary(16) NOT NULL,
                `identifier` varchar(255) NOT NULL,
                `transaction_id` varchar(255) NOT NULL,
                `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE(`identifier`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
