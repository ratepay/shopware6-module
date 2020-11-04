<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;


use PHPUnit\Framework\TestCase;
use RatePAY\Model\Request\SubModel\Head\External;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class ExternalFactoryTest extends TestCase
{

    use KernelTestBehaviour;


    public function testGetData()
    {
        $externalFactory = new ExternalFactory(new EventDispatcher(), new RequestStack());

        $requestData = $this->createRequestData('DHL Versand');
        /** @var External $external */
        $external = $externalFactory->getData($requestData);

        self::assertInstanceOf(External::class, $external);
        self::assertInstanceOf(External\Tracking::class, $external->getTracking());
        self::assertEquals('DHL', $external->getTracking()->getProvider());
        self::assertEquals('my-code-1', $external->getTracking()->getId());
    }

    public function testGetDataNoProvider()
    {
        $externalFactory = new ExternalFactory(new EventDispatcher(), new RequestStack());

        $requestData = $this->createRequestData('anything else');

        /** @var External $external */
        $external = $externalFactory->getData($requestData);
        self::assertInstanceOf(External::class, $external);
        self::assertInstanceOf(External\Tracking::class, $external->getTracking());
        self::assertNull($external->getTracking()->getProvider());
    }

    public function testGetDataFail()
    {
        $externalFactory = new ExternalFactory(new EventDispatcher(), new RequestStack());

        $requestData = $this->createRequestData(null);

        $external = $externalFactory->getData($requestData);
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

        return new OrderOperationData($order, '', [], false);
    }
}
