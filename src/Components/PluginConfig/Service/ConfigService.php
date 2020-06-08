<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PluginConfig\Service;


use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigService
{

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
    }

    public function getDeviceFingerprintSnippetId()
    {
        $config = $this->getPluginConfiguration();
        return $config['ratepayDevicefingerprintingSnippetId'] ?? 'ratepay';
    }

    protected function getPluginConfiguration(): array
    {
        return $this->systemConfigService->get('RatepayPayments.config', null) ? : [];
    }


}
