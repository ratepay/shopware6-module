<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PluginConfig\Service;


use Ratepay\RatepayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\PrepaymentPaymentHandler;
use Ratepay\RatepayPayments\RatepayPayments;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\PluginCollection;
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

    public function getDeviceFingerprintSnippetId()
    {
        $config = $this->getPluginConfiguration();
        return $config['ratepayDevicefingerprintingSnippetId'] ?? 'ratepay';
    }

    public function getPluginConfiguration(): array
    {
        return $this->systemConfigService->get('RatepayPayments.config', null) ?: [];
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
