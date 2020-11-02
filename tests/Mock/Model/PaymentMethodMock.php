<?php declare(strict_types=1);

namespace Ratepay\RpayPayments\Tests\Mock\Model;

use Ratepay\RpayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InvoicePaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\PrepaymentPaymentHandler;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class PaymentMethodMock
{

    public const METHODS = [
        DebitPaymentHandler::class => [
            'id' => '2dc19599207d4be9ae78bffb348e944e',
            'handler' => DebitPaymentHandler::class
        ],
        InstallmentPaymentHandler::class => [
            'id' => '555c0b6ee4a149a687ed891d2fd0ae3e',
            'handler' => InstallmentPaymentHandler::class
        ],
        InstallmentZeroPercentPaymentHandler::class => [
            'id' => 'e3989f3a124a4aa1806abb576bfef671',
            'handler' => InstallmentZeroPercentPaymentHandler::class
        ],
        InvoicePaymentHandler::class => [
            'id' => '63445822a9f34e11b212a99fd723a08c',
            'handler' => InvoicePaymentHandler::class
        ],
        PrepaymentPaymentHandler::class => [
            'id' => 'c820224bd6154613b2bfd47545838022',
            'handler' => PrepaymentPaymentHandler::class
        ]
    ];

    public static function createCollection(array $handlers = null): EntityCollection
    {
        return new EntityCollection(static::createArray($handlers));
    }

    public static function createArray(array $handlers = null): array
    {
        $handlers = $handlers ?? static::METHODS;
        $rtn = [];
        foreach ($handlers as $method) {
            $mock = static::createMock($method);
            $rtn[$mock->getId()] = $mock;
        }
        return $rtn;
    }

    public static function createMock($handler): PaymentMethodEntity
    {
        $method = new PaymentMethodEntity();
        $method->setId(static::METHODS[$handler]['id']);
        $method->setHandlerIdentifier(static::METHODS[$handler]['handler']);
        return $method;
    }
}
