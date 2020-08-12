<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Bootstrap;


use Ratepay\RatepayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\PrepaymentPaymentHandler;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class PaymentMethods extends AbstractBootstrap
{

    private const PAYMENT_METHODS = [
        [
            'handlerIdentifier' => InvoicePaymentHandler::class,
            'name' => 'Ratepay Rechnung',
            'description' => 'Kauf auf Rechnung',
            'afterOrderEnabled' => true,
        ],
        [
            'handlerIdentifier' => PrepaymentPaymentHandler::class,
            'name' => 'Ratepay Vorkasse',
            'description' => 'Kauf per Vorkasse',
            'afterOrderEnabled' => true,
        ],
        [
            'handlerIdentifier' => DebitPaymentHandler::class,
            'name' => 'Ratepay Lastschrift',
            'description' => 'Kauf per SEPA Lastschrift',
            'afterOrderEnabled' => true,
        ],
        [
            'handlerIdentifier' => InstallmentPaymentHandler::class,
            'name' => 'Ratepay Ratenzahlung',
            'description' => 'Kauf per Ratenzahlung',
            'afterOrderEnabled' => true,
        ],
        [
            'handlerIdentifier' => InstallmentZeroPercentPaymentHandler::class,
            'name' => 'Ratepay 0% Finanzierung',
            'description' => 'Kauf per 0% Finanzierung',
            'afterOrderEnabled' => true,
        ],
    ];

    /**
     * @var EntityRepositoryInterface
     */
    private $paymentRepository;

    public function injectServices(): void
    {
        $this->paymentRepository = $this->container->get('payment_method.repository');
    }

    public function update(): void
    {
        $this->install();
    }

    public function install(): void
    {
        foreach (self::PAYMENT_METHODS as $paymentMethod) {
            $paymentMethod['pluginId'] = $this->plugin->getId();
            $this->paymentRepository->upsert([$paymentMethod], $this->defaultContext);
        }

        $this->setActiveFlags(true);
    }

    public function uninstall($keepUserData = false): void
    {
        $this->setActiveFlags(false);
    }

    public function activate(): void
    {
        $this->setActiveFlags(true);
    }

    public function deactivate(): void
    {
        $this->setActiveFlags(false);
    }

    protected function setActiveFlags(bool $activated): void
    {
        $paymentEntities = $this->paymentRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('pluginId', $this->plugin->getId())),
            $this->defaultContext
        );

        $updateData = array_map(static function (PaymentMethodEntity $entity) use ($activated) {
            return [
                'id' => $entity->getId(),
                'active' => $activated,
            ];
        }, $paymentEntities->getElements());

        $this->paymentRepository->update(array_values($updateData), $this->defaultContext);
    }
}
