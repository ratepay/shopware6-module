<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler;


use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Exception\SyncPaymentProcessException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class InvoicePaymentHandler extends AbstractPaymentHandler
{

    const RATEPAY_METHOD = 'INVOICE';

    /**
     * The pay function will be called after the customer completed the order.
     * Allows to process the order and store additional information.
     *
     * Throw a @throws SyncPaymentProcessException
     * @see SyncPaymentProcessException exception if an error ocurres while processing the payment
     *
     */
    public function XXpay(SyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): void
    {
        // TODO: Implement pay() method.
        throw new SyncPaymentProcessException($transaction->getOrderTransaction()->getId(), 'hihi bestellung abgebrochen');
    }
}
