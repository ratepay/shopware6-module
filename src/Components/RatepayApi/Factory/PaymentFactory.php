<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Factory;


use RatePAY\Model\Request\SubModel\Content\Payment;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;

class PaymentFactory
{


    public function getData(OrderTransactionEntity $transaction, RequestDataBag $requestDataBag)
    {
        $payment = new Payment();

        $handler = $transaction->getPaymentMethod()->getHandlerIdentifier();
        $ratepayMethod = constant($handler . '::RATEPAY_METHOD');
        $payment->setMethod($ratepayMethod);
        $payment->setAmount($transaction->getAmount()->getTotalPrice());

        if (false) { // TODO
            $installment = $paymentRequestData->getInstallmentDetails();


            $data = array_merge($data, [
                'DebitPayType' => $installment->getPaymentSubtype(),
                'Amount' => $installment->getTotalAmount(),
                'InstallmentDetails' => [
                    'InstallmentNumber' => $installment->getNumberOfRatesFull(),
                    'InstallmentAmount' => $installment->getRate(),
                    'LastInstallmentAmount' => $installment->getLastRate(),
                    'InterestRate' => $installment->getInterestRate(),
                    'PaymentFirstday' => $installment->getPaymentFirstday()
                ]
            ]);
        }
        return $payment;
    }
}
