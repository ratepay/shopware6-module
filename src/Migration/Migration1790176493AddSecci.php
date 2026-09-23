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
use Ratepay\RpayPayments\Components\PaymentHandler\LegacyPaymentHandler;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\Checkout\Payment\Aggregate\PaymentMethodTranslation\PaymentMethodTranslationDefinition;
use Shopware\Core\Checkout\Payment\PaymentMethodDefinition;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1790176493AddSecci extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1790176493;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `ratepay_profile_config_method`
                ADD `require_secci` tinyint(1) NOT NULL DEFAULT 0 AFTER `allow_different_addresses`;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
