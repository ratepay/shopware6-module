<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Bootstrap;

use Doctrine\DBAL\Connection;
use Ratepay\RpayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\PrepaymentPaymentHandler;
use Shopware\Core\Checkout\Payment\PaymentMethodDefinition;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class PaymentMethods extends AbstractBootstrap
{
    final public const PAYMENT_METHODS = [
        [
            'handlerIdentifier' => InvoicePaymentHandler::class,
            'name' => 'Ratepay Rechnung',
            'description' => 'Kauf auf Rechnung',
            'afterOrderEnabled' => true,
            'technicalName' => 'ratepay_invoice',
        ],
        [
            'handlerIdentifier' => PrepaymentPaymentHandler::class,
            'name' => 'Ratepay Vorkasse',
            'description' => 'Kauf per Vorkasse',
            'afterOrderEnabled' => true,
            'technicalName' => 'ratepay_prepayment',
        ],
        [
            'handlerIdentifier' => DebitPaymentHandler::class,
            'name' => 'Ratepay Lastschrift',
            'description' => 'Kauf per SEPA Lastschrift',
            'afterOrderEnabled' => true,
            'technicalName' => 'ratepay_debit',
        ],
        [
            'handlerIdentifier' => InstallmentPaymentHandler::class,
            'name' => 'Ratepay Ratenzahlung',
            'description' => 'Kauf per Ratenzahlung',
            'afterOrderEnabled' => true,
            'technicalName' => 'ratepay_installment',
        ],
        [
            'handlerIdentifier' => InstallmentZeroPercentPaymentHandler::class,
            'name' => 'Ratepay 0% Finanzierung',
            'description' => 'Kauf per 0% Finanzierung',
            'afterOrderEnabled' => true,
            'technicalName' => 'ratepay_installment_zero_percent',
        ],
    ];

    private ?EntityRepository $paymentRepository = null;

    private PaymentMethodDefinition $paymentMethodDefinition;

    private Connection $connection;

    public function injectServices(): void
    {
        $this->paymentRepository = $this->container->get('payment_method.repository');
        $this->paymentMethodDefinition = $this->container->get(PaymentMethodDefinition::class);
        $this->connection = $this->container->get(Connection::class);
    }

    public function update(): void
    {
    }

    public function postUpdate(): void
    {
        $this->addPaymentMethods();
        $this->setTechnicalNames();
    }

    public function postInstall(): void
    {
        $this->addPaymentMethods();
        $this->setActiveFlags(false);
    }

    public function install(): void
    {
    }

    public function uninstall(bool $keepUserData = false): void
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

    private function setActiveFlags(bool $activated): void
    {
        /** @var PaymentMethodEntity[] $paymentEntities */
        $paymentEntities = $this->paymentRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('pluginId', $this->plugin->getId())),
            $this->defaultContext
        )->getElements();

        $updateData = array_map(static fn (PaymentMethodEntity $entity): array => [
            'id' => $entity->getId(),
            'active' => $activated,
        ], $paymentEntities);

        $this->paymentRepository->update(array_values($updateData), $this->defaultContext);
    }

    private function addPaymentMethods(): void
    {
        // add payment methods which does not exist yet
        $upsertData = [];
        foreach (self::PAYMENT_METHODS as $paymentMethod) {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('handlerIdentifier', $paymentMethod['handlerIdentifier']));
            $criteria->setLimit(1);

            $id = $this->paymentRepository->searchIds($criteria, $this->defaultContext)->firstId();

            if ($id === null) {
                if (!$this->isFieldTechnicalNameAvailable()) {
                    unset($paymentMethod['technicalName']);
                }

                $paymentMethod['pluginId'] = $this->plugin->getId();
                $paymentMethod['active'] = false;
                $upsertData[] = $paymentMethod;
            }
        }

        if ($upsertData !== []) {
            $this->paymentRepository->upsert($upsertData, $this->defaultContext);
        }
    }

    private function setTechnicalNames(): void
    {
        if (!$this->isFieldTechnicalNameAvailable()) {
            return;
        }

        $storageNames = [
            'technicalName' => $this->getStorageName('technicalName'),
            'handlerIdentifier' => $this->getStorageName('handlerIdentifier'),
            'pluginId' => $this->getStorageName('pluginId'),
        ];

        foreach (self::PAYMENT_METHODS as $paymentMethod) {
            $this->connection->update(
                PaymentMethodDefinition::ENTITY_NAME,
                [
                    $storageNames['technicalName'] => $paymentMethod['technicalName'],
                ],
                [
                    $storageNames['handlerIdentifier'] => $paymentMethod['handlerIdentifier'],
                    $storageNames['pluginId'] => Uuid::fromHexToBytes($this->plugin->getId()),
                    $storageNames['technicalName'] => null,
                ]
            );
        }
    }

    private function isFieldTechnicalNameAvailable(): bool
    {
        return $this->paymentMethodDefinition->getField('technicalName') instanceof Field;
    }

    private function getStorageName(string $propertyName): string
    {
        $field = $this->paymentMethodDefinition->getField($propertyName);

        return $field instanceof StorageAware ? $field->getStorageName() : $propertyName;
    }
}
