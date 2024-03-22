<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Core;

use Ratepay\RpayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class PluginConfigService
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    public function getDeviceFingerprintSnippetId(): string
    {
        $config = $this->getPluginConfiguration();

        return $config['ratepayDevicefingerprintingSnippetId'] ?? 'ratepay';
    }

    public function getPluginConfiguration(): array
    {
        return $this->systemConfigService->get('RpayPayments.config', null) ?: [];
    }

    public function isAutoOperationBasedOnDeliveryStatusEnabled(): bool
    {
        $config = $this->getPluginConfiguration();

        return $config['bidirectionalityEnabled'] ?? false;
    }

    public function getSubmitAdditionalAddress(): string
    {
        $config = $this->getPluginConfiguration();

        return $config['additionalAddressLine'] ?? 'disabled';
    }

    public function getPaymentStatusForMethod(PaymentMethodEntity $paymentMethod): ?string
    {
        $config = $this->getPluginConfiguration();
        switch ($paymentMethod->getHandlerIdentifier()) {
            case InvoicePaymentHandler::class:
                return $config['paymentStatusInvoice'] ?? null;
            case DebitPaymentHandler::class:
                return $config['paymentStatusDebit'] ?? null;
            case InstallmentPaymentHandler::class:
                return $config['paymentStatusInstallment'] ?? null;
            case InstallmentZeroPercentPaymentHandler::class:
                return $config['paymentStatusInstallment0Percent'] ?? null;
            default:
                return null;
        }
    }

    public function isSendDiscountsAsCartItem(): bool
    {
        $config = $this->getPluginConfiguration();

        return (bool) ($config['sendDiscountsAsCartItem'] ?? false);
    }

    public function isSendShippingCostsAsCartItem(): bool
    {
        $config = $this->getPluginConfiguration();

        return (bool) ($config['sendShippingCostsAsCartItem'] ?? false);
    }

    public function isAutoDeliveryOfVirtualProductsDisabled(): bool
    {
        $config = $this->getPluginConfiguration();

        return (bool) ($config['disableAutoDeliveryOfVirtualProducts'] ?? false);
    }

    public function isUpdatePaymentStatusOnOrderItemOperation(): bool
    {
        $config = $this->getPluginConfiguration();

        return (bool) ($config['updatePaymentStatus'] ?? false);
    }

    public function isUpdateDeliveryStatusOnOrderItemOperation(): bool
    {
        $config = $this->getPluginConfiguration();

        return (bool) ($config['updateDeliveryStatus'] ?? false);
    }

    protected function getContext(): Context
    {
        return Context::createDefaultContext();
    }
}
