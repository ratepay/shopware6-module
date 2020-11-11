<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\ProfileConfig\Service;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use RatePAY\Model\Response\ProfileRequest;
use Ratepay\RpayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodInstallmentEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\ProfileConfigResponseConverter;
use Ratepay\RpayPayments\Tests\Mock\Model\PaymentMethodMock;
use Ratepay\RpayPayments\Tests\Mock\Repository\PaymentMethodRepositoryMock;

class ProfileConfigResponseConverterTest extends TestCase
{
    public function testFullConvert(): void
    {
        $profileRequestResponse = $this->getProfileRequestResponse([
            'merchantConfig' => [
                'merchant-status' => 2, // status active
                'country-code-billing' => 'AB,CD,EF',
                'country-code-delivery' => 'GH',
                'currency' => 'EUR',

                // Invoice
                'activation-status-invoice' => 2, // status active
                'tx-limit-invoice-min' => 10,
                'tx-limit-invoice-max' => 100,
                'tx-limit-invoice-max-b2b' => 10000,
                'b2b-invoice' => 'yes',
                'delivery-address-invoice' => 'yes',

                // Debit
                'activation-status-elv' => 1,  // status inactive

                // Installment
                'activation-status-installment' => 2, // status active
                'tx-limit-installment-min' => 20,
                'tx-limit-installment-max' => 200,
                'tx-limit-installment-max-b2b' => 20000,
                'b2b-installment' => 'no',
                'delivery-address-installment' => 'no',
            ],
            'installmentConfig' => [
                'valid-payment-firstdays' => '2,28',
                'month-allowed' => '3,6,12,24,48',
                'rate-min-normal' => '9',
                'interestrate-min' => '3',
            ],
        ]);

        $paymentMethodsRepo = new PaymentMethodRepositoryMock(PaymentMethodMock::createArray([
            DebitPaymentHandler::class, InvoicePaymentHandler::class, InstallmentPaymentHandler::class,
        ]));

        $converter = new ProfileConfigResponseConverter($paymentMethodsRepo);
        $results = $converter->convert($profileRequestResponse, '123');
        self::assertCount(3, $results);

        [$profileConfigData, $methodConfigs, $installmentConfigs] = $results;
        self::assertCount(2, $methodConfigs);
        self::assertCount(1, $installmentConfigs);

        self::assertTrue($profileConfigData[ProfileConfigEntity::FIELD_STATUS]);

        self::assertCount(3, $profileConfigData[ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING]);
        self::assertCount(1, $profileConfigData[ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING]);

        self::assertTrue($profileConfigData[ProfileConfigEntity::FIELD_STATUS]);

        // compare method configs
        foreach ($methodConfigs as $methodConfig) {
            $methodId = $methodConfig[ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD_ID];
            switch ($methodId) {
                case PaymentMethodMock::METHODS[InvoicePaymentHandler::class]['id']:
                    self::assertEquals(10, $methodConfig[ProfileConfigMethodEntity::FIELD_LIMIT_MIN]);
                    self::assertEquals(100, $methodConfig[ProfileConfigMethodEntity::FIELD_LIMIT_MAX]);
                    self::assertEquals(10000, $methodConfig[ProfileConfigMethodEntity::FIELD_LIMIT_MAX_B2B]);
                    self::assertTrue($methodConfig[ProfileConfigMethodEntity::FIELD_ALLOW_B2B]);
                    self::assertTrue($methodConfig[ProfileConfigMethodEntity::FIELD_ALLOW_DIFFERENT_ADDRESSES]);
                    break;
                case PaymentMethodMock::METHODS[InstallmentPaymentHandler::class]['id']:
                    self::assertEquals(20, $methodConfig[ProfileConfigMethodEntity::FIELD_LIMIT_MIN]);
                    self::assertEquals(200, $methodConfig[ProfileConfigMethodEntity::FIELD_LIMIT_MAX]);
                    self::assertEquals(20000, $methodConfig[ProfileConfigMethodEntity::FIELD_LIMIT_MAX_B2B]);
                    self::assertFalse($methodConfig[ProfileConfigMethodEntity::FIELD_ALLOW_B2B]);
                    self::assertFalse($methodConfig[ProfileConfigMethodEntity::FIELD_ALLOW_DIFFERENT_ADDRESSES]);
                    break;
                default:
                    self::fail('there is one not expected methodConfig: ' . json_encode($methodConfig));
            }
        }

        // compare installment config (should be only one)
        self::assertCount(5, $installmentConfigs[0][ProfileConfigMethodInstallmentEntity::FIELD_ALLOWED_MONTHS]);
        self::assertEquals(9, $installmentConfigs[0][ProfileConfigMethodInstallmentEntity::FIELD_RATE_MIN]);
        self::assertTrue($installmentConfigs[0][ProfileConfigMethodInstallmentEntity::FIELD_IS_BANKTRANSFER_ALLOWED]);
        self::assertTrue($installmentConfigs[0][ProfileConfigMethodInstallmentEntity::FIELD_IS_DEBIT_ALLOWED]);
    }

    public function testInvalidZeroPercent()
    {
        $profileRequestResponse = $this->getProfileRequestResponse([
            'merchantConfig' => [
                'merchant-status' => 2, // status active
                'country-code-billing' => 'AB,CD,EF',
                'country-code-delivery' => 'GH',
                'currency' => 'EUR',

                // Installment
                'activation-status-installment' => 2, // status active
                'tx-limit-installment-min' => 20,
                'tx-limit-installment-max' => 200,
                'tx-limit-installment-max-b2b' => 20000,
                'b2b-installment' => 'no',
                'delivery-address-installment' => 'no',
            ],
            'installmentConfig' => [
                'valid-payment-firstdays' => '2',
                'month-allowed' => '3',
                'rate-min-normal' => '9',
                // this value should prevent creating the installment/method-config cause the normal
                // installment must have a min-rate of zero
                'interestrate-min' => '3',
            ],
        ]);

        $paymentMethodsRepo = new PaymentMethodRepositoryMock(PaymentMethodMock::createArray([
            InstallmentZeroPercentPaymentHandler::class,
        ]));

        $converter = new ProfileConfigResponseConverter($paymentMethodsRepo);
        $results = $converter->convert($profileRequestResponse, '123');
        self::assertCount(3, $results);

        [$profileConfigData, $methodConfigs, $installmentConfigs] = $results;
        self::assertCount(0, $methodConfigs);
        self::assertCount(0, $installmentConfigs);
    }

    public function testInvalidInstallment()
    {
        $profileRequestResponse = $this->getProfileRequestResponse([
            'merchantConfig' => [
                'merchant-status' => 2, // status active
                'country-code-billing' => 'AB,CD,EF',
                'country-code-delivery' => 'GH',
                'currency' => 'EUR',

                // Installment
                'activation-status-installment' => 2, // status active
                'tx-limit-installment-min' => 20,
                'tx-limit-installment-max' => 200,
                'tx-limit-installment-max-b2b' => 20000,
                'b2b-installment' => 'no',
                'delivery-address-installment' => 'no',
            ],
            'installmentConfig' => [
                'valid-payment-firstdays' => '2',
                'month-allowed' => '3',
                'rate-min-normal' => '9',
                // this value should prevent creating the installment/method-config cause the normal
                // installment can not have a min-rate of zero
                'interestrate-min' => '0',
            ],
        ]);

        $paymentMethodsRepo = new PaymentMethodRepositoryMock(PaymentMethodMock::createArray([
            InstallmentPaymentHandler::class,
        ]));

        $converter = new ProfileConfigResponseConverter($paymentMethodsRepo);
        $results = $converter->convert($profileRequestResponse, '123');
        self::assertCount(3, $results);

        [$profileConfigData, $methodConfigs, $installmentConfigs] = $results;
        self::assertNotNull($profileConfigData);
        self::assertCount(0, $methodConfigs);
        self::assertCount(0, $installmentConfigs);
    }

    private function getProfileRequestResponse(array $result): ProfileRequest
    {
        $profileRequestResponse = $this->createMock(ProfileRequest::class);
        $profileRequestResponse->method('isSuccessful')->willReturn(true);
        $profileRequestResponse->method('getResult')->willReturn(new ArrayObject($result));

        return $profileRequestResponse;
    }
}
