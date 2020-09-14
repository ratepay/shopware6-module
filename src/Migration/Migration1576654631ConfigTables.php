<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1576654631ConfigTables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1576654631;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE `ratepay_profile_config` (
              `id` binary(16) NOT NULL,
              `profile_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `security_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `sales_channel_id` binary(16) NOT NULL,
              `backend` tinyint(1) DEFAULT 0,
              `country_code_billing` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `sandbox` tinyint(1) DEFAULT 0,
              `country_code_delivery` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `currency` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `status` tinyint(1) DEFAULT 0,
              `status_message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `created_at` datetime NOT NULL,
              `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeQuery('
            CREATE TABLE `ratepay_profile_config_method` (
              `id` binary(16) NOT NULL,
              `profile_id` binary(16) NOT NULL,
              `payment_method_id` binary(16) NOT NULL,
              `allow_b2b` tinyint(1) DEFAULT 0,
              `limit_min` int(11) NULL DEFAULT NULL,
              `limit_max` int(11) NULL DEFAULT NULL,
              `limit_max_b2b` int(11) NULL DEFAULT NULL,
              `allow_different_addresses` tinyint(1) NOT NULL,
              `created_at` datetime NOT NULL,
              `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`profile_id`) REFERENCES `ratepay_profile_config` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
                FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

        $connection->executeQuery('
            CREATE TABLE `ratepay_profile_config_method_installment` (
                `id` binary(16) NOT NULL,
                `month_allowed` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `is_banktransfer_allowed` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 0,
                `is_debit_allowed` tinyint(1) DEFAULT 0,
                `rate_min_normal` tinyint(1) NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                FOREIGN KEY (`id`) REFERENCES `ratepay_profile_config_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');

    }

    public function updateDestructive(Connection $connection): void
    {

    }
}
