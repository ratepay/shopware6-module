<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Bootstrap;


use Monolog\Logger;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class AbstractBootstrap implements ContainerAwareInterface
{

    use ContainerAwareTrait;

    /**
     * @var InstallContext
     */
    protected $installContext;


    /**
     * @var Context
     */
    protected $defaultContext;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var PluginEntity
     */
    protected $plugin;

    public final function __construct()
    {
        $this->defaultContext = Context::createDefaultContext();
    }

    public abstract function install();

    public abstract function update();

    public abstract function uninstall($keepUserData = false);

    public abstract function activate();

    public abstract function deactivate();

    public function injectServices(): void
    {

    }

    public final function setInstallContext(InstallContext $installContext)
    {
        $this->installContext = $installContext;
    }

    public final function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    public final function setPlugin(PluginEntity $plugin)
    {
        $this->plugin = $plugin;
    }

    public function preInstall()
    {
    }

    public function preUpdate()
    {
    }

    public function preUninstall($keepUserData = false)
    {
    }

    public function preActivate()
    {
    }

    public function preDeactivate()
    {
    }

    public function postActivate()
    {
    }

    public function postDeactivate()
    {
    }

    public function postUninstall()
    {
    }

    public function postUpdate()
    {
    }

    public function postInstall()
    {
    }

    protected final function getPluginPath()
    {
        return $this->container->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . $this->plugin->getPath();
    }


}
