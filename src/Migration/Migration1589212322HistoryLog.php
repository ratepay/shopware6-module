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
                `orderId` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
                `event` varchar(100) COLLATE utf8_unicode_ci NULL,
                `articlename` varchar(100) COLLATE utf8_unicode_ci NULL,
                `articlenumber` varchar(100) COLLATE utf8_unicode_ci NULL,
                `quantity` int(5) COLLATE utf8_unicode_ci NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
        $connection->executeQuery("DROP TABLE ratepay_order_history");
    }
}
