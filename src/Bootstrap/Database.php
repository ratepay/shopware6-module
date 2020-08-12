<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Bootstrap;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

class Database extends AbstractBootstrap
{
    /**
     * @var Connection
     */
    protected $connection;

    public function injectServices(): void
    {
        $this->connection = $this->container->get(Connection::class);
    }

    public function install(): void
    {
    }

    public function update(): void
    {
    }

    /**
     * @throws DBALException
     */
    public function uninstall(bool $keepUserData = false): void
    {
        if ($keepUserData) {
            return;
        }

        $this->connection->exec("SET FOREIGN_KEY_CHECKS=0;");
        $this->connection->exec('DROP TABLE IF EXISTS `ratepay_profile_config`');
        $this->connection->exec('DROP TABLE IF EXISTS `ratepay_profile_config_method_installment`');
        $this->connection->exec('DROP TABLE IF EXISTS `ratepay_profile_config_method`');
        $this->connection->exec('DROP TABLE IF EXISTS `ratepay_api_log`');
        $this->connection->exec('DROP TABLE IF EXISTS `ratepay_order_history`');
        $this->connection->exec('DROP TABLE IF EXISTS `ratepay_order_data`');
        $this->connection->exec('DROP TABLE IF EXISTS `ratepay_order_line_item_data`');
        $this->connection->exec('DROP TABLE IF EXISTS `ratepay_position`');
        $this->connection->exec('DROP TABLE IF EXISTS `ratepay_payment_lock`');
        $this->connection->exec("SET FOREIGN_KEY_CHECKS=1;");
    }

    public function activate(): void
    {
    }

    public function deactivate(): void
    {
    }
}
