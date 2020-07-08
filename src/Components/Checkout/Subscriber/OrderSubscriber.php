<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Subscriber;


use Ratepay\RatepayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RatepayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\PrepaymentPaymentHandler;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Shopware\Core\Framework\Context;

class OrderSubscriber implements EventSubscriberInterface
{

    protected const RATEPAY_PAYMENT_HANDLER = [
        PrepaymentPaymentHandler::class,
        InstallmentPaymentHandler::class,
        InstallmentZeroPercentPaymentHandler::class,
        DebitPaymentHandler::class,
        InvoicePaymentHandler::class,
    ];

    /**
     * @var EntityRepositoryInterface
     */
    protected $orderTransactionRepository;

    public function __construct(EntityRepositoryInterface $orderTransactionRepository)
    {
        $this->orderTransactionRepository = $orderTransactionRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderEvents::ORDER_LOADED_EVENT => 'onOrderLoaded',
        ];
    }

    public function onOrderLoaded(EntityLoadedEvent $event): void
    {
        $orders = $event->getEntities();
        /** @var OrderEntity $order */
        foreach ($orders as $order) {
            if ($this->isPayedWithRatepay($order, $event->getContext())) {
                $order->addExtension(
                    'ratepayData',
                    new RatepayOrderDataEntity()
                );
            }
        }
    }

    protected function isPayedWithRatepay(OrderEntity $order, Context $context): bool
    {
        // ToDo: Better way to detect if it is a ratepay order?

        // Because not every order entity provided here has the transaction association
        // loaded, we will load the transactions manually
        $transactions = $this->getOrderTransactions($order, $context);

        foreach ($transactions as $transaction) {
            $payment = $transaction->getPaymentMethod();
            if ($payment && in_array($payment->getHandlerIdentifier(), self::RATEPAY_PAYMENT_HANDLER, true)) {
                return true;
            }
        }

        return false;
    }

    protected function getOrderTransactions(OrderEntity $order, Context $context): OrderTransactionCollection
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('orderId', $order->getId()));
        $criteria->addAssociation('paymentMethod');
        /** @var OrderTransactionCollection $transactions */
        $transactions = $this->orderTransactionRepository->search($criteria, $context)->getEntities();
        return $transactions;
    }
}
