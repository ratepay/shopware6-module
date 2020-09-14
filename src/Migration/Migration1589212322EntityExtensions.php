<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1589212322EntityExtensions extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1589212322;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery("
            CREATE TABLE `ratepay_position` (
              `id` binary(16) NOT NULL,
              `delivered` int(11) NOT NULL DEFAULT '0',
              `canceled` int(11) NOT NULL DEFAULT '0',
              `returned` int(11) NOT NULL DEFAULT '0',
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");

        $connection->executeQuery("
            CREATE TABLE `ratepay_order_data` (
              `id` binary(16) NOT NULL,
              `order_id` binary(16) NOT NULL,
              `order_version_id` binary(16) NOT NULL,
              `profile_id` varchar(255) NOT NULL,
              `shipping_position_id` binary(16) NULL,
              `transaction_id` varchar(255) COLLATE utf8mb4_bin NOT NULL,
              `descriptor` varchar(255) COLLATE utf8mb4_bin NULL,
              `successful` TINYINT NOT NULL, 
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME NULL,
              PRIMARY KEY (`id`),
              FOREIGN KEY (`order_id`,`order_version_id`) REFERENCES `order` (`id`, `version_id`) ON UPDATE CASCADE,
              FOREIGN KEY (`shipping_position_id`) REFERENCES `ratepay_position` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");

        $connection->executeQuery("
            CREATE TABLE `ratepay_order_line_item_data` (
              `id` binary(16) NOT NULL,
              `order_line_item_id` binary(16) NOT NULL,
              `order_line_item_version_id` binary(16) NOT NULL,
              `position_id` binary(16) NOT NULL,
              `created_at` DATETIME NOT NULL,
              `updated_at` DATETIME NULL,
              PRIMARY KEY (`id`),
              FOREIGN KEY (`order_line_item_id`,`order_line_item_version_id`) REFERENCES `order_line_item` (`id`, `version_id`) ON UPDATE CASCADE,
              FOREIGN KEY (`position_id`) REFERENCES `ratepay_position` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ");
    }

    public function updateDestructive(Connection $connection): void
    {

    }
}
