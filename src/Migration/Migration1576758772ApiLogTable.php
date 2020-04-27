<?php declare(strict_types=1);

namespace RatePay\RatePayPayments\Migration;

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
                `version` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
                `operation` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `suboperation` varchar(255) COLLATE utf8_unicode_ci NULL,
                `transaction_id` varchar(255) COLLATE utf8_unicode_ci NULL,
                `firstname` varchar(255) COLLATE utf8_unicode_ci NULL,
                `lastname` varchar(255) COLLATE utf8_unicode_ci NULL,
                `request` longtext COLLATE utf8_unicode_ci NOT NULL,
                `response` longtext COLLATE utf8_unicode_ci NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
