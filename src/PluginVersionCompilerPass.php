<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PluginVersionCompilerPass implements CompilerPassInterface
{
    public function __construct(
        private readonly string $pluginDir
    ) {
    }

    public function process(ContainerBuilder $container): void
    {
        $container->setParameter('ratepay.shopware_payment.plugin_version', $this->getPluginVersion());
    }

    private function getPluginVersion(): string
    {
        $composerJsonString = file_get_contents($this->pluginDir . 'composer.json');
        $composerJson = json_decode((string) $composerJsonString, true, 512, JSON_THROW_ON_ERROR);

        return is_array($composerJson) ? $composerJson['version'] : '0.0.1-dev';
    }
}
