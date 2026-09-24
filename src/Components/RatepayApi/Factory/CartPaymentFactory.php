<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use RatePAY\Model\Request\SubModel\Content\Payment;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\CheckoutOperationInterface;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OperationDataWithCart;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;

/**
 * @extends AbstractFactory<Payment>
 */
class CartPaymentFactory extends AbstractFactory
{
    protected function isSupported(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof OperationDataWithCart;
    }

    protected function _getData(AbstractRequestData $requestData): ?object
    {
        /** @var OperationDataWithCart $requestData */

        $handler = $requestData->getPaymentMethod()->getHandlerIdentifier();
        $ratepayMethod = constant($handler . '::RATEPAY_METHOD');

        $payment = new Payment();
        $payment->setMethod($ratepayMethod);
        $payment->setCurrency($requestData->getCurrency()->getIsoCode());
        $payment->setAmount($requestData->getCart()->getPrice()->getTotalPrice());

        return $payment;
    }
}
