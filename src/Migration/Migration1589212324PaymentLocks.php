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

class Migration1589212324PaymentLocks extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1589212324;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
        CREATE TABLE `ratepay_payment_lock` (
            `id` binary(16) NOT NULL,
            `customer_id` binary(16) NOT NULL,
            `payment_method_id` binary(16) NOT NULL,
            `locked_until` datetime NOT NULL,
            `created_at` DATETIME NOT NULL,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            CONSTRAINT fk_ratepay_payment_lock__customer FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT fk_ratepay_payment_lock__payment_method FOREIGN KEY (`payment_method_id`) REFERENCES `payment_method` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
