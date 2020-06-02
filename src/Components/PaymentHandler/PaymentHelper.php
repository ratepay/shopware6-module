<?php

namespace Ratepay\RatepayPayments\Components\PaymentHandler;

use Ratepay\RatepayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\PrepaymentPaymentHandler;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentHelper
{
    const RATEPAY_PAYMENT_METHODS_HANDLER_IDENTIFIER = [
        DebitPaymentHandler::class,
        InstallmentPaymentHandler::class,
        InstallmentZeroPercentPaymentHandler::class,
        InvoicePaymentHandler::class,
        PrepaymentPaymentHandler::class
    ];

    public function isRatepayPaymentsSelected(SalesChannelContext $context): bool
    {
        return in_array($context->getPaymentMethod()->getHandlerIdentifier(), self::RATEPAY_PAYMENT_METHODS_HANDLER_IDENTIFIER, true);
    }

    public function bankAccountRequired(SalesChannelContext $context): bool
    {
        return $context->getPaymentMethod()->getHandlerIdentifier() == DebitPaymentHandler::class;
    }

    public function isInstallmentMethod(SalesChannelContext $context): bool
    {
        return $context->getPaymentMethod()->getHandlerIdentifier() == InstallmentPaymentHandler::class;
    }

    public function isZeroPercentInstallment(SalesChannelContext $context): bool
    {
        return $context->getPaymentMethod()->getHandlerIdentifier() == InstallmentZeroPercentPaymentHandler::class;
    }

}
