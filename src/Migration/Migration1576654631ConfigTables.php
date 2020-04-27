<?php declare(strict_types=1);

namespace RatePay\RatePayPayments\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1576654631ConfigTables extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1576654631;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE `ratepay_profile_config_method` (
                `id` binary(16) NOT NULL,
                `b2b` tinyint(1) NOT NULL,
                `limit_min` int(11) NOT NULL,
                `limit_max` int(11) NOT NULL,
                `limit_max_b2b` int(11) DEFAULT NULL,
                `allow_different_addresses` tinyint(1) NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        $connection->executeQuery('
            CREATE TABLE `ratepay_profile_config_method_installment` (
                `payment_config_id` binary(16) NOT NULL,
                `month_allowed` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `payment_firstday` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
                `interestrate_default` double NOT NULL,
                `rate_min_normal` double NOT NULL,
                PRIMARY KEY (`payment_config_id`),
                CONSTRAINT `FK_B3353A06C6DCBE74` FOREIGN KEY (`payment_config_id`) REFERENCES `ratepay_profile_config_method` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        $connection->executeQuery('
            CREATE TABLE `ratepay_profile_config` (
                `id` binary(16) NOT NULL,
                `profile_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `security_code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                `sales_channel_id` binary(16) NOT NULL,
                `backend` tinyint(1) NOT NULL,
                `zero_percent_installment` tinyint(1) NOT NULL,
                `country_code_billing` varchar(2) COLLATE utf8_unicode_ci NOT NULL,
                `sandbox` tinyint(1) NOT NULL,
                `country_code_delivery` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
                `currency` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
                `config_invoice_id` binary(16) DEFAULT NULL,
                `config_debit_id` binary(16) DEFAULT NULL,
                `config_installment_id` binary(16) DEFAULT NULL,
                `config_prepayment_id` binary(16) DEFAULT NULL,
                `error_default` tinytext COLLATE utf8_unicode_ci DEFAULT NULL,
                `status` tinyint(1) COLLATE utf8_unicode_ci DEFAULT 0,
                `status_message` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` datetime NOT NULL,
                -- PRIMARY KEY (`sales_channel_id`,`backend`,`zero_percent_installment`,`country_code_billing`),
                PRIMARY KEY (`id`),
                CONSTRAINT `FK_536585423194FB7D` FOREIGN KEY (`config_installment_id`) REFERENCES `ratepay_profile_config_method` (`id`),
                CONSTRAINT `FK_5365854271806BDB` FOREIGN KEY (`config_invoice_id`) REFERENCES `ratepay_profile_config_method` (`id`),
                CONSTRAINT `FK_53658542758934E4` FOREIGN KEY (`config_prepayment_id`) REFERENCES `ratepay_profile_config_method` (`id`),
                CONSTRAINT `FK_53658542F9866C30` FOREIGN KEY (`config_debit_id`) REFERENCES `ratepay_profile_config_method` (`id`),
                CONSTRAINT `FK_53722542F9866C30` FOREIGN KEY (`sales_channel_id`) REFERENCES `sales_channel` (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
