<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Bootstrap;

use Shopware\Core\System\SystemConfig\SystemConfigService;

class PluginConfiguration extends AbstractBootstrap
{
    private SystemConfigService $configService;

    public function injectServices(): void
    {
        $this->configService = $this->container->get(SystemConfigService::class);
    }

    public function install(): void
    {
        // we set this in the bootstrap, because we want to prevent that the flag is enabled after a plugin update and configuration save.
        $this->configService->set('RpayPayments.config.bidirectionalityEnabled', true);
        $this->configService->set('RpayPayments.config.updatePaymentStatus', true);
        $this->configService->set('RpayPayments.config.updateDeliveryStatus', true);
    }

    public function update(): void
    {
    }

    public function uninstall(bool $keepUserData = false): void
    {
    }

    public function activate(): void
    {
    }

    public function deactivate(): void
    {
    }
}
