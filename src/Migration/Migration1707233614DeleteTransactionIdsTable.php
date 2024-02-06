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

class Migration1707233614DeleteTransactionIdsTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1707233614;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            DROP TABLE IF EXISTS ratepay_transaction_id_temp;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
