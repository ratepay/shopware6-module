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

class Migration1635241712AddInstallmentDefaultPayment extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1635241712;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `ratepay_profile_config_method_installment`
                ADD `default_payment_type` ENUM(\'DIRECT-DEBIT\',\'BANK-TRANSFER\') NOT NULL DEFAULT \'DIRECT-DEBIT\' AFTER `rate_min_normal`;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
