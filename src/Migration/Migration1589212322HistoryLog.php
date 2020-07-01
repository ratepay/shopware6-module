<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1589212322HistoryLog extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1589212322;
    }

    public function update(Connection $connection): void
    {

        $connection->executeQuery("
            CREATE TABLE `ratepay_order_history` (
                `id` binary(16) NOT NULL,
                `order_id` binary(16) NOT NULL,
                `order_version_id` BINARY(16) NOT NULL,
                `event` varchar(100) COLLATE utf8mb4_unicode_ci NULL,
                `user` varchar(100) COLLATE utf8mb4_unicode_ci NULL,
                `product_name` varchar(100) COLLATE utf8mb4_unicode_ci NULL,
                `product_number` varchar(100) COLLATE utf8mb4_unicode_ci NULL,
                `quantity` int(5) COLLATE utf8mb4_unicode_ci NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.ratepay_order_history.order_id` FOREIGN KEY (`order_id`, `order_version_id`)
                  REFERENCES `order` (`id`, `version_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
        $connection->executeQuery("DROP TABLE ratepay_order_history");
    }
}
