<?php


namespace Ratepay\RatepayPayments\Components\RatepayApi\Factory;


use Exception;
use RatePAY\Model\Request\SubModel\Content\Payment;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;

class PaymentFactory
{


    public function getData(OrderTransactionEntity $transaction)
    {
        $payment = new Payment();

        $handler = $transaction->getPaymentMethod()->getHandlerIdentifier();
        $ratepayMethod = constant($handler.'::RATEPAY_METHOD');
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
