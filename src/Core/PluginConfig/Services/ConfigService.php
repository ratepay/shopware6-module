<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Core\PluginConfig\Services;


use Ratepay\RatepayPayments\RatepayPayments as RatepayPayments;
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
                ->addFilter(new EqualsFilter('baseClass', RatepayPayments::class))
                ->setLimit(1),
            $this->getContext()
        );
        return $plugins->first()->getVersion();
    }

    protected function getContext() {
        return Context::createDefaultContext();
    }

}
