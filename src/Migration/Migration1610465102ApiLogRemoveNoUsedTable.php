<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1610465102ApiLogRemoveNoUsedTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1610465102;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('ALTER TABLE `ratepay_api_log` DROP `updated_at`');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
