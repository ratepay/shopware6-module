<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PluginConfig\Service;

use Ratepay\RpayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\PrepaymentPaymentHandler;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfigService
{
    private SystemConfigService $systemConfigService;

    public function __construct(SystemConfigService $systemConfigService)
    {
        $this->systemConfigService = $systemConfigService;
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

    protected function getContext(): Context
    {
        return Context::createDefaultContext();
    }

    public function isBidirectionalityEnabled(): bool
    {
        $config = $this->getPluginConfiguration();

        return $config['bidirectionalityEnabled'] ?? false;
    }

    public function getBidirectionalityFullDelivery(): string
    {
        $config = $this->getPluginConfiguration();

        return $config['bidirectionalityStatusFullDelivery'] ?? '';
    }

    public function getBidirectionalityFullCancel(): string
    {
        $config = $this->getPluginConfiguration();

        return $config['bidirectionalityStatusFullCancel'] ?? '';
    }

    public function getBidirectionalityFullReturn(): string
    {
        $config = $this->getPluginConfiguration();

        return $config['bidirectionalityStatusFullReturn'] ?? '';
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
            case PrepaymentPaymentHandler::class:
                return $config['paymentStatusPrepayment'] ?? null;
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
}
