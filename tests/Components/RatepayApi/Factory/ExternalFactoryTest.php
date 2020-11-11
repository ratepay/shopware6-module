<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\RatepayApi\Factory;

use PHPUnit\Framework\TestCase;
use RatePAY\Model\Request\SubModel\Head\External;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Factory\Mock;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;

class ExternalFactoryTest extends TestCase
{
    use KernelTestBehaviour;

    public function testGetData()
    {
        $factory = Mock::createExternalFactory();

        $requestData = $this->createRequestData('DHL Versand');
        /** @var External $external */
        $external = $factory->getData($requestData);

        self::assertInstanceOf(External::class, $external);
        self::assertInstanceOf(External\Tracking::class, $external->getTracking());
        self::assertEquals('DHL', $external->getTracking()->getProvider());
        self::assertEquals('my-code-1', $external->getTracking()->getId());
    }

    public function testGetDataNoProvider()
    {
        $factory = Mock::createExternalFactory();

        $requestData = $this->createRequestData('anything else');

        /** @var External $external */
        $external = $factory->getData($requestData);
        self::assertInstanceOf(External::class, $external);
        self::assertInstanceOf(External\Tracking::class, $external->getTracking());
        self::assertNull($external->getTracking()->getProvider());
    }

    public function testGetDataFail()
    {
        $factory = Mock::createExternalFactory();

        $requestData = $this->createRequestData(null);

        $external = $factory->getData($requestData);
        self::assertNull($external);
    }

    private function createRequestData($methodCode)
    {
        $order = new OrderEntity();
        $order->setDeliveries(new OrderDeliveryCollection([]));
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

        return new OrderOperationData(Context::createDefaultContext(), $order, '', [], false);
    }
}
