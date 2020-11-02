<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use Exception;
use PHPUnit\Framework\TestCase;
use RatePAY\Model\Request\SubModel\Content\Payment;
use Ratepay\RpayPayments\Bootstrap\PaymentMethods;
use Ratepay\RpayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\PrepaymentPaymentHandler;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Tests\Mock\Model\PaymentMethodMock;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class PaymentFactoryTest extends TestCase
{
    use KernelTestBehaviour;

    public function testGetData(): void
    {

        /** @var PaymentFactory $paymentFactory */
        $paymentFactory = new PaymentFactory(new EventDispatcher(), new RequestStack());

        foreach (PaymentMethods::PAYMENT_METHODS as $method) {
            $paymentRequestData = $this->createPaymentRequestData($method['handlerIdentifier']);
            /** @var Payment $payment */
            $payment = $paymentFactory->getData($paymentRequestData);

            self::assertEquals(123.456, $payment->getAmount());

            switch ($method['handlerIdentifier']) {
                case DebitPaymentHandler::class:
                    self::assertEquals('ELV', $payment->getMethod());
                    break;
                case InstallmentPaymentHandler::class:
                case InstallmentZeroPercentPaymentHandler::class:
                    self::assertEquals('INSTALLMENT', $payment->getMethod());
                    break;
                case InvoicePaymentHandler::class:
                    self::assertEquals('INVOICE', $payment->getMethod());
                    break;
                case PrepaymentPaymentHandler::class:
                    self::assertEquals('PREPAYMENT', $payment->getMethod());
                    break;
                default:
                    throw new Exception('there is one missing test for payment handler: ' . $method['handlerIdentifier']);
            }
        }
    }

    private function createPaymentRequestData(string $handlerClass)
    {
        $paymentRequestData = $this->createMock(PaymentRequestData::class);
        $paymentRequestData->method('getTransaction')->willReturn(new OrderTransactionEntity());
        $priceMock = $this->createMock(CalculatedPrice::class);
        $priceMock->method('getTotalPrice')->willReturn(123.456);
        $paymentRequestData->getTransaction()->setAmount($priceMock);

        $paymentRequestData->getTransaction()->setPaymentMethod(PaymentMethodMock::createMock($handlerClass));
        return $paymentRequestData;
    }

}
