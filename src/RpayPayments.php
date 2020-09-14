<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Ratepay\RpayPayments;

use Exception;
use Ratepay\RpayPayments\Bootstrap\AbstractBootstrap;
use Ratepay\RpayPayments\Bootstrap\Database;
use Ratepay\RpayPayments\Bootstrap\PaymentMethods;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Kernel;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Routing\RouteCollectionBuilder;

class RpayPayments extends Plugin
{
    public function install(Plugin\Context\InstallContext $context): void
    {
        $bootstrapper = $this->getBootstrapClasses($context);
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->preInstall();
        }
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->install();
        }
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->postInstall();
        }
    }

    /**
     * @param Plugin\Context\InstallContext $context
     * @return AbstractBootstrap[]
     */
    protected function getBootstrapClasses(Plugin\Context\InstallContext $context)
    {
        /** @var AbstractBootstrap[] $bootstrapper */
        $bootstrapper = [
            new Database(),
            new PaymentMethods()
        ];

        /** @var EntityRepositoryInterface $pluginRepository */
        $pluginRepository = $this->container->get('plugin.repository');
        $plugins = $pluginRepository->search((new Criteria())->addFilter(new EqualsFilter('baseClass', get_class($this))), Context::createDefaultContext());
        $plugin = $plugins->first();
        //$logger = new FileLogger($this->container->getParameter('kernel.logs_dir'));
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->setInstallContext($context);
            //$bootstrap->setLogger($logger);
            $bootstrap->setContainer($this->container);
            $bootstrap->injectServices();
            $bootstrap->setPlugin($plugin);

        }
        return $bootstrapper;
    }

    public function update(Plugin\Context\UpdateContext $context): void
    {
        $bootstrapper = $this->getBootstrapClasses($context);
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->preUpdate();
        }
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->update();
        }
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->postUpdate();
        }
    }

    public function uninstall(Plugin\Context\UninstallContext $context): void
    {
        $bootstrapper = $this->getBootstrapClasses($context);
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->preUninstall();
        }
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->uninstall($context->keepUserData());
        }
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->postUninstall();
        }
    }

    public function deactivate(Plugin\Context\DeactivateContext $context): void
    {
        $bootstrapper = $this->getBootstrapClasses($context);
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->preDeactivate();
        }
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->deactivate();
        }
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->postDeactivate();
        }
    }

    public function activate(Plugin\Context\ActivateContext $context): void
    {
        $bootstrapper = $this->getBootstrapClasses($context);
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->preActivate();
        }
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->activate();
        }
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->postActivate();
        }
    }


    public function boot(): void
    {
        parent::boot();
        if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
            require_once __DIR__ . '/../vendor/autoload.php';
        } else {
            throw new Exception('Ratepay: the autoloader has not been created! Please run `composer install` in Ratepay plugin directory');
        }
    }

    public function configureRoutes(RouteCollectionBuilder $routes, string $environment): void
    {
        parent::configureRoutes($routes, $environment);
        $routes->import(__DIR__ . '/Components/**/DependencyInjection/routes' . Kernel::CONFIG_EXTS, '/', 'glob');
    }

    public function build(ContainerBuilder $containerBuilder): void
    {
        parent::build($containerBuilder);

        $componentContainerFiles = [
            'services.xml',
            'models.xml',
            'controller.xml',
            'subscriber.xml',
        ];

        $loader = new XmlFileLoader($containerBuilder, new FileLocator(__DIR__));
        foreach (array_filter(glob(__DIR__ . '/Components/*'), 'is_dir') as $dir) {
            foreach ($componentContainerFiles as $fileName) {
                $file = $dir . '/DependencyInjection/' . $fileName;
                if (file_exists($file)) {
                    $loader->load($file);
                }
            }
        }

    }
}