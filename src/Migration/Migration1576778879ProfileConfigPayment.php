<?php declare(strict_types=1);

namespace Ratepay\RatepayPayments\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1576778879ProfileConfigPayment extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1576778879;
    }

    public function update(Connection $connection): void
    {
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
