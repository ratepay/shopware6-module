<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\RatepayApi\Factory;

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
use Ratepay\RpayPayments\Components\RatepayApi\Factory\PaymentFactory;
use Ratepay\RpayPayments\Tests\Mock\Model\PaymentMethodMock;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Factory\Mock;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Symfony\Component\EventDispatcher\EventDispatcher;

class PaymentFactoryTest extends TestCase
{
    use KernelTestBehaviour;

    public function testMethodNamesAndAmount(): void
    {
        $factory = $this->getFactory();

        foreach (PaymentMethods::PAYMENT_METHODS as $method) {
            $paymentRequestData = $this->createPaymentRequestData($method['handlerIdentifier']);
            $payment = $factory->getData($paymentRequestData);

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
        $paymentRequestData->method('getContext')->willReturn(Context::createDefaultContext());
        $priceMock = $this->createMock(CalculatedPrice::class);
        $priceMock->method('getTotalPrice')->willReturn(123.456);
        $paymentRequestData->getTransaction()->setAmount($priceMock);

        $paymentRequestData->getTransaction()->setPaymentMethod(PaymentMethodMock::createMock($handlerClass));

        return $paymentRequestData;
    }

    private function getFactory(): PaymentFactory
    {
        return new PaymentFactory(new EventDispatcher());
    }
}
