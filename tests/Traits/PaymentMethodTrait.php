<?php
declare(strict_types=1);

namespace Ratepay\RpayPayments\Tests\Traits;

use Ratepay\RpayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\PrepaymentPaymentHandler;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

trait PaymentMethodTrait
{

    protected function getInvoiceEntity(): PaymentMethodEntity
    {
        return $this->createPaymentMethod(InvoicePaymentHandler::class, 'Ratepay invoice');
    }

    protected function getDebitEntity(): PaymentMethodEntity
    {
        return $this->createPaymentMethod(DebitPaymentHandler::class, 'Ratepay debit');
    }

    protected function getPrepaymentEntity(): PaymentMethodEntity
    {
        return $this->createPaymentMethod(PrepaymentPaymentHandler::class, 'Ratepay prepayment');
    }

    private function createPaymentMethod(string $paymentHandler, string $name): PaymentMethodEntity
    {
        $context = Context::createDefaultContext();
        /** @var EntityRepositoryInterface $repository */
        $repository = $this->getContainer()->get('payment_method.repository');

        $criteria = new Criteria([]);
        $criteria->addFilter(new EqualsFilter('handlerIdentifier', $paymentHandler));
        if ($entity = $repository->search($criteria, $context)->first()) {
            return $entity;
        }

        $id = Uuid::randomHex();
        $repository->upsert([
            [
                'id' => $id,
                'handlerIdentifier' => $paymentHandler,
                'name' => $name
            ]
        ], $context);

        return $this->createPaymentMethod($paymentHandler, $name);
    }
}
