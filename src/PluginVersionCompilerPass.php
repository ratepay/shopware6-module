<?php declare(strict_types=1);

namespace Ratepay\RpayPayments;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PluginVersionCompilerPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $pluginDir;

    public function __construct(string $pluginDir)
    {
        $this->pluginDir = $pluginDir;
    }

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $container->setParameter('ratepay.shopware_payment.plugin_version', $this->getPluginVersion());
    }

    private function getPluginVersion(): string
    {
        $composerJsonString = file_get_contents($this->pluginDir);
        $composerJson = json_decode($composerJsonString, true);
        return $composerJson['version'];
    }
}
