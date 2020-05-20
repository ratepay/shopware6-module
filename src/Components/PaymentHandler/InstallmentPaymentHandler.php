<?php


namespace Ratepay\RatepayPayments\Components\PaymentHandler;


use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Exception\SyncPaymentProcessException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class InstallmentPaymentHandler extends AbstractPaymentHandler
{

    const RATEPAY_METHOD = 'INSTALLMENT';

}
