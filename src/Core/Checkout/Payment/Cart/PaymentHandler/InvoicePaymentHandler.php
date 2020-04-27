<?php


namespace RatePay\RatePayPayments\Core\Checkout\Payment\Cart\PaymentHandler;


use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\SynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Exception\SyncPaymentProcessException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class InvoicePaymentHandler implements SynchronousPaymentHandlerInterface
{

    /**
     * The pay function will be called after the customer completed the order.
     * Allows to process the order and store additional information.
     *
     * Throw a @throws SyncPaymentProcessException
     * @see SyncPaymentProcessException exception if an error ocurres while processing the payment
     *
     */
    public function pay(SyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): void
    {
        // TODO: Implement pay() method.
        throw new SyncPaymentProcessException($transaction->getOrderTransaction()->getId(), 'hihi bestellung abgebrochen');
    }
}
