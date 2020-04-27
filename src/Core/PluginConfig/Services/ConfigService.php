<?php


namespace RatePay\RatePayPayments\Core\PluginConfig\Services;


use RatePay\RatePayPayments\RatePayPayments as RatePayPayments;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigService
{

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;
    /**
     * @var EntityRepositoryInterface
     */
    private $pluginRepository;

    public function __construct(SystemConfigService $systemConfigService, EntityRepositoryInterface $pluginRepository)
    {
        $this->systemConfigService = $systemConfigService;
        $this->pluginRepository = $pluginRepository;
    }

    public function getPluginVersion(): string
    {
        /** @var PluginCollection $plugin */
        $plugins = $this->pluginRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('baseClass', RatePayPayments::class))
                ->setLimit(1),
            $this->getContext()
        );
        return $plugins->first()->getVersion();
    }

    protected function getContext() {
        return Context::createDefaultContext();
    }

}
