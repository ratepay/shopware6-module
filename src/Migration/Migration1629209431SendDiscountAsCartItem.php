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

class Migration1629209431SendDiscountAsCartItem extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1629209431;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement("
            ALTER TABLE `ratepay_order_data` ADD `send_discount_as_cart_item` TINYINT(1) NOT NULL DEFAULT '0' AFTER `successful`;
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
