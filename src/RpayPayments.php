<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments;

use Ratepay\RpayPayments\Bootstrap\AbstractBootstrap;
use Ratepay\RpayPayments\Bootstrap\Database;
use Ratepay\RpayPayments\Bootstrap\PaymentMethods;
use Ratepay\RpayPayments\Core\FeatureFlagService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class RpayPayments extends Plugin
{
    public function install(InstallContext $installContext): void
    {
        $bootstrapper = $this->getBootstrapClasses($installContext);
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->preInstall();
        }

        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->install();
        }
    }

    public function postInstall(InstallContext $installContext): void
    {
        $bootstrapper = $this->getBootstrapClasses($installContext);

        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->postInstall();
        }
    }

    public function update(UpdateContext $updateContext): void
    {
        $bootstrapper = $this->getBootstrapClasses($updateContext);
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->preUpdate();
        }

        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->update();
        }
    }

    public function postUpdate(UpdateContext $updateContext): void
    {
        $bootstrapper = $this->getBootstrapClasses($updateContext);

        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->postUpdate();
        }
    }

    public function uninstall(UninstallContext $context): void
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

    public function deactivate(DeactivateContext $context): void
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

    public function activate(ActivateContext $context): void
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

    public function executeComposerCommands(): bool
    {
        return true;
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

        $containerBuilder->addCompilerPass(new PluginVersionCompilerPass(__DIR__ . '/../'));

        $locator = new FileLocator('Resources/config');
        $resolver = new LoaderResolver([
            new YamlFileLoader($containerBuilder, $locator),
            new GlobFileLoader($containerBuilder, $locator),
            new DirectoryLoader($containerBuilder, $locator),
        ]);
        (new DelegatingLoader($resolver))
            ->load(\rtrim((string) $this->getPath(), '/') . '/Resources/config' . '/{packages}/*.yaml', 'glob');
    }

    public function boot(): void
    {
        parent::boot();

        FeatureFlagService::loadFeatureFlags($this->container->get(SystemConfigService::class)->get('RpayPayments.config.featureFlags'));
    }

    /**
     * @return AbstractBootstrap[]
     */
    protected function getBootstrapClasses(InstallContext $context): array
    {
        /** @var AbstractBootstrap[] $bootstrapper */
        $bootstrapper = [
            new Database(),
            new PaymentMethods(),
        ];

        /** @var EntityRepository $pluginRepository */
        $pluginRepository = $this->container->get('plugin.repository');
        /** @var Plugin\PluginEntity $plugin */
        $plugin = $pluginRepository->search((new Criteria())->addFilter(new EqualsFilter('baseClass', static::class)), Context::createDefaultContext())->first();
        // $logger = new FileLogger($this->container->getParameter('kernel.logs_dir'));
        foreach ($bootstrapper as $bootstrap) {
            $bootstrap->setInstallContext($context);
            // $bootstrap->setLogger($logger);
            $bootstrap->setContainer($this->container);
            $bootstrap->injectServices();
            $bootstrap->setPlugin($plugin);
        }

        return $bootstrapper;
    }
}
