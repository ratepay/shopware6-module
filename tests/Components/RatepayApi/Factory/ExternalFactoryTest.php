<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\RatepayApi\Factory;

use PHPUnit\Framework\TestCase;
use RatePAY\Model\Request\SubModel\Head\External;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ExternalFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\AbstractRequest;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Factory\Mock;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ExternalFactoryTest extends TestCase
{
    use KernelTestBehaviour;

    public function testGetData()
    {
        $factory = $this->getFactory();

        $requestData = $this->createRequestData('DHL Versand', OrderOperationData::OPERATION_DELIVER);
        $external = $factory->getData($requestData);

        self::assertInstanceOf(External::class, $external);
        self::assertEquals('order-number', $external->getOrderId());
        self::assertInstanceOf(External\Tracking::class, $external->getTracking());
        self::assertEquals('DHL', $external->getTracking()->getProvider());
        self::assertEquals('my-code-1', $external->getTracking()->getId());
    }

    public function testOrderNumber()
    {
        $factory = $this->getFactory();

        $requestData = $this->createRequestData('DHL Versand', OrderOperationData::OPERATION_REQUEST);
        $external = $factory->getData($requestData);
        self::assertInstanceOf(External::class, $external);
        self::assertEquals('order-number', $external->getOrderId());

        $requestData = $this->createRequestData('DHL Versand', OrderOperationData::OPERATION_DELIVER);
        $external = $factory->getData($requestData);
        self::assertInstanceOf(External::class, $external);
        self::assertEquals('order-number', $external->getOrderId());

    }

    public function testCustomerNumber()
    {
        $factory = $this->getFactory();

        $requestData = $this->createRequestData('DHL Versand', OrderOperationData::OPERATION_REQUEST);
        $external = $factory->getData($requestData);
        self::assertInstanceOf(External::class, $external);
        self::assertEquals('customer-number', $external->getMerchantConsumerId());

        $requestData = $this->createRequestData('DHL Versand', OrderOperationData::OPERATION_DELIVER);
        $external = $factory->getData($requestData);
        self::assertInstanceOf(External::class, $external);
        self::assertNull($external->getMerchantConsumerId());
    }

    public function testTrackingInvalidProvider()
    {
        $factory = $this->getFactory();

        $requestData = $this->createRequestData('anything else', OrderOperationData::OPERATION_DELIVER);

        $external = $factory->getData($requestData);

        self::assertInstanceOf(External::class, $external);
        self::assertInstanceOf(External\Tracking::class, $external->getTracking());
        self::assertEquals(ExternalFactory::SHIPPING_PROVIDER_OTHER, $external->getTracking()->getProvider());
    }

    public function testTrackingNull()
    {
        $factory = $this->getFactory();

        $requestData = $this->createRequestData(null, OrderOperationData::OPERATION_DELIVER);

        $external = $factory->getData($requestData);
        self::assertNotNull($external);
        self::assertNull($external->getTracking());
    }

    private function createRequestData($methodCode, $operation): OrderOperationData
    {
        $order = new OrderEntity();
        $order->setOrderNumber('order-number');
        $order->setDeliveries(new OrderDeliveryCollection([]));

        $orderCustomer = new OrderCustomerEntity();
        $orderCustomer->setId(Uuid::randomHex());
        $orderCustomer->setEmail('phpunit@dev.local');
        $orderCustomer->setCustomerNumber('customer-number');
        $orderCustomer->setFirstName('firstname');
        $orderCustomer->setLastName('lastname');
        $order->setOrderCustomer($orderCustomer);

        if ($methodCode !== null) {
            $document1 = new OrderDeliveryEntity();
            $document1->setId(Uuid::randomHex());
            $document1->setTrackingCodes(['my-code-1', 'my-code-2']);
            $document1->setShippingMethod(new ShippingMethodEntity());
            $document1->getShippingMethod()->setName($methodCode);
            $order->getDeliveries()->add($document1);

            $document2 = new OrderDeliveryEntity();
            $document2->setId(Uuid::randomHex());
            $document2->setTrackingCodes(['my-code-3', 'my-code-4']);
            $document1->setShippingMethod(new ShippingMethodEntity());
            $document1->getShippingMethod()->setName($methodCode);
            $order->getDeliveries()->add($document2);
        }

        return new OrderOperationData(Context::createDefaultContext(), $order, $operation, [], false);
    }

    private function getFactory(): ExternalFactory
    {
        return new ExternalFactory(new EventDispatcher());
    }
}
