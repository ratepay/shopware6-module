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

class Migration1576758772ApiLogTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1576758772;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery("
            CREATE TABLE `ratepay_api_log` (
                `id` binary(16) NOT NULL,
                `version` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
                `operation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `sub_operation` varchar(255) COLLATE utf8mb4_unicode_ci NULL,
                `result` varchar(255) COLLATE utf8mb4_unicode_ci NULL,
                `request` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                `response` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
                `additional_data` longtext COLLATE utf8mb4_unicode_ci NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NULL
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
        $connection->executeQuery("DROP TABLE ratepay_api_log");
    }
}
