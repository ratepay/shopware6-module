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
    /**
     * @var EntityRepositoryInterface
     */
    private $paymentRepository;

    public function injectServices(): void
    {
        $this->paymentRepository = $this->container->get('payment_method.repository');
    }

    public function update()
    {
        $this->install();
    }

    public function install()
    {
        $payments = [
            [
                'handlerIdentifier' => InvoicePaymentHandler::class,
                'name' => 'RatePAY Kauf auf Rechnung',
                'description' => 'Zahlen Sie erst nach Erhalt der Ware',
                'pluginId' => $this->plugin->getId()
            ],
            [
                'handlerIdentifier' => PrepaymentPaymentHandler::class,
                'name' => 'RatePAY Vorkasse',
                'description' => 'TEXT TEXT TEXT TEXT',
                'pluginId' => $this->plugin->getId()
            ],
            [
                'handlerIdentifier' => DebitPaymentHandler::class,
                'name' => 'RatePAY Lastschrift',
                'description' => 'TEXT TEXT TEXT TEXT',
                'pluginId' => $this->plugin->getId()
            ],
            [
                'handlerIdentifier' => InstallmentPaymentHandler::class,
                'name' => 'RatePAY Ratenzahlung',
                'description' => 'TEXT TEXT TEXT TEXT',
                'pluginId' => $this->plugin->getId()
            ],
            [
                'handlerIdentifier' => InstallmentZeroPercentPaymentHandler::class,
                'name' => 'RatePAY 0% Finanzierung',
                'description' => 'TEXT TEXT TEXT TEXT',
                'pluginId' => $this->plugin->getId()
            ]
        ];

        foreach ($payments as $index => $payment) {
            $paymentEntities = $this->paymentRepository->search(
                ((new Criteria())
                    ->addFilter(new EqualsFilter('pluginId', $this->plugin->getId()))
                    ->addFilter(new EqualsFilter('handlerIdentifier', $payment['handlerIdentifier']))
                    ->setLimit(1)
                ),
                $this->defaultContext
            );
            $paymentEntity = $paymentEntities->first();
            if ($paymentEntity !== null) {
                unset($payments[$index]);
            }
        }
        if(count($payments) > 0) {
            $this->paymentRepository->upsert(array_values($payments), $this->defaultContext);
        }
        $this->setActiveFlags(true);
    }

    protected function setActiveFlags(bool $activated)
    {
        $paymentEntities = $this->paymentRepository->search(
            ((new Criteria())
                ->addFilter(new EqualsFilter('pluginId', $this->plugin->getId()))
                ->setLimit(1)
            ),
            $this->defaultContext
        );

        $updateData = array_map(function (PaymentMethodEntity $entity) use ($activated) {
            return [
                'id' => $entity->getId(),
                'active' => $activated
            ];
        }, $paymentEntities->getElements());

        $this->paymentRepository->update(array_values($updateData), $this->defaultContext);
    }

    public function uninstall($keepUserData = false)
    {
        $this->setActiveFlags(false);
    }

    public function activate()
    {
        $this->setActiveFlags(true);
    }

    public function deactivate()
    {
        $this->setActiveFlags(false);
    }
}
