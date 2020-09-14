<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;


use RatePAY\Model\Request\SubModel\Content\Payment;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;

class PaymentFactory extends AbstractFactory
{

    protected function _getData(IRequestData $requestData): ?object
    {

        /** @var PaymentRequestData $requestData */
        $transaction = $requestData->getTransaction();
        $payment = new Payment();

        $handler = $transaction->getPaymentMethod()->getHandlerIdentifier();
        $ratepayMethod = constant($handler . '::RATEPAY_METHOD');
        $payment->setMethod($ratepayMethod);
        $payment->setAmount($transaction->getAmount()->getTotalPrice());

        return $payment;
    }
}
