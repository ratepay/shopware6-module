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

class Migration1650647563ExtendApiLogGui extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1650647563;
    }

    public function update(Connection $connection): void
    {
        // column `result` can be safely dropped cause the value is still persist in the `response` column
        $connection->executeStatement('
            ALTER TABLE `ratepay_api_log`
                DROP `result`,
                ADD `result_code` VARCHAR(8) NOT NULL AFTER `sub_operation`,
                ADD `result_text` TEXT NOT NULL AFTER `result_code`,
                ADD `status_code` VARCHAR(8) NOT NULL AFTER `result_text`,
                ADD `status_text` TEXT NOT NULL AFTER `status_code`,
                ADD `reason_code` VARCHAR(8) NOT NULL AFTER `status_text`,
                ADD `reason_text` TEXT NOT NULL AFTER `reason_code`;
        ');

        $connection->executeStatement("
            UPDATE ratepay_api_log SET
                result_code = ExtractValue(response, '/response/head/processing/result/attribute::code'),
                result_text = ExtractValue(response, '/response/head/processing/result'),
                status_code = ExtractValue(response, '/response/head/processing/status/attribute::code'),
                status_text = ExtractValue(response, '/response/head/processing/status'),
                reason_code = ExtractValue(response, '/response/head/processing/reason/attribute::code'),
                reason_text = ExtractValue(response, '/response/head/processing/reason')
        ");
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
