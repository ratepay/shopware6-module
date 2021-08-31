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

class Migration1615564688AdminOrder extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1615564688;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE `ratepay_admin_order_token` (
                `id` binary(16) NOT NULL,
                `token` varchar(255) NOT NULL,
                `sales_channel_id` binary(16) NOT NULL,
                `sales_channel_domain_id` binary(16) NOT NULL,
                `cart_token` varchar(255) NULL,
                `valid_until` datetime NOT NULL,
                `created_at` datetime NOT NULL,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (`sales_channel_domain_id`) REFERENCES `sales_channel_domain`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ');

        $connection->executeStatement("
            UPDATE `ratepay_profile_config` SET `backend` = 0 WHERE `backend` IS NULL
        ");
        $connection->executeStatement("
            UPDATE `ratepay_profile_config` SET `sandbox` = 0 WHERE `sandbox` IS NULL
        ");

        $connection->executeStatement("
            ALTER TABLE `ratepay_profile_config`
                CHANGE `backend` `only_admin_orders` TINYINT(1) NOT NULL DEFAULT '0',
                CHANGE `sandbox` `sandbox` TINYINT(1) NOT NULL DEFAULT '0';
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
