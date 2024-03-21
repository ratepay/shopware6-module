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

class Migration1710932783DeletePrepayment extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1710932783;
    }

    public function update(Connection $connection): void
    {
        $handlerClass = 'Ratepay\RpayPayments\Components\PaymentHandler\PrepaymentPaymentHandler';

        $connection->executeStatement(
            sprintf('
                DELETE p FROM %s as p
                LEFT JOIN %s t ON (t.payment_method_id = p.id)
                WHERE
                    t.id IS NULL AND
                    p.handler_identifier = :handler
            ', PaymentMethodDefinition::ENTITY_NAME, OrderTransactionDefinition::ENTITY_NAME),
            [
                'handler' => $handlerClass,
            ]
        );

        // add the legacy/removed-text to name - if method still exist
        $connection->executeStatement(
            sprintf('
                UPDATE %s as t
                INNER JOIN %s p ON t.payment_method_id = p.id
                SET
                    t.name = CONCAT(name, " (legacy / removed)")
                WHERE
                    p.handler_identifier = :handler
            ', PaymentMethodTranslationDefinition::ENTITY_NAME, PaymentMethodDefinition::ENTITY_NAME),
            [
                'handler' => $handlerClass,
            ]
        );

        // replace handler/disable method - if method still exist
        $connection->update(
            PaymentMethodDefinition::ENTITY_NAME,
            [
                'active' => 0,
                'handler_identifier' => LegacyPaymentHandler::class,
            ],
            [
                'handler_identifier' => $handlerClass,
            ]
        );
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
