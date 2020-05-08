<?php


namespace Ratepay\RatepayPayments\Bootstrap;


use Doctrine\DBAL\Connection;

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

    public function install()
    {
    }

    public function update()
    {
    }

    public function uninstall($keepUserData = false)
    {
        if ($keepUserData) {
            return;
        }

        $this->connection->exec("SET FOREIGN_KEY_CHECKS=0;");
        $this->connection->exec('DROP TABLE IF EXISTS `ratepay_profile_config`');
        $this->connection->exec('DROP TABLE IF EXISTS `ratepay_profile_config_method_installment`');
        $this->connection->exec('DROP TABLE IF EXISTS `ratepay_profile_config_method`');
        $this->connection->exec('DROP TABLE IF EXISTS `ratepay_api_log`');
        $this->connection->exec("SET FOREIGN_KEY_CHECKS=1;");
    }

    public function activate()
    {
    }

    public function deactivate()
    {
    }
}
