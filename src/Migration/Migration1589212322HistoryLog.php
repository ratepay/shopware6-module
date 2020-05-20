<?php declare(strict_types=1);

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
                `orderId` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                `date` datetime NOT NULL,
                `event` varchar(100) COLLATE utf8_unicode_ci NULL,
                `articlename` varchar(100) COLLATE utf8_unicode_ci NULL,
                `articlenumber` varchar(100) COLLATE utf8_unicode_ci NULL,
                `quantity` varchar(100) COLLATE utf8_unicode_ci NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
        $connection->executeQuery("DROP TABLE ratepay_order_history");
    }
}
