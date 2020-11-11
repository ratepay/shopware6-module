<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\RatepayApi\Service\Request;

use RatePAY\Model\Request\SubModel\Content\Invoicing;
use RatePAY\Model\Request\SubModel\Content\ShoppingBasket;
use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ExternalFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\InvoiceFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\AbstractRequest;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Service\Request\PaymentDeliverServiceMock;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Service\Request\PaymentRequestServiceMock;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;

class PaymentDeliverServiceTest extends AbstractRequestService
{
    use KernelTestBehaviour;

    public function testGetRequestContent(): void
    {
        /** @var PaymentRequestServiceMock $service */
        $service = $this->getServiceMock();

        $requestData = $this->createEmptyRequestDataDto();

        $content = $service->getRequestContent($requestData);
        $head = $service->getRequestHead($requestData);

        self::assertNotNull($content->getShoppingBasket(), 'basket must be set for PaymentDeliver');
        self::assertNull($content->getCustomer(), 'customer is not required for PaymentDeliver');
        self::assertNull($content->getPayment(), 'payment is not required for PaymentDeliver');
        self::assertNull($content->getInvoicing(), 'invoice should be `null` by default');
    }

    public function testGetRequestContentWithInvoice(): void
    {
        /** @var PaymentRequestServiceMock $service */
        $service = $this->getServiceMock(true);

        $content = $service->getRequestContent($this->createEmptyRequestDataDto());
        self::assertNotNull($content->getInvoicing());
    }

    protected function getServiceMock($withInvoice = false): ?AbstractRequest
    {
        $headFactory = $this->createMock(HeadFactory::class);
        $headFactory->method('getData')->willReturn(new Head());

        $shoppingBasketFactory = $this->createMock(ShoppingBasketFactory::class);
        $shoppingBasketFactory->method('getData')->willReturn(new ShoppingBasket());

        $customerFactory = $this->createMock(InvoiceFactory::class);
        $customerFactory->method('getData')->willReturn($withInvoice ? new Invoicing() : null);

        $paymentFactory = $this->createMock(ExternalFactory::class);
        $paymentFactory->method('getData')->willReturn(new Head\External());

        return new PaymentDeliverServiceMock($headFactory, $shoppingBasketFactory, $customerFactory, $paymentFactory);
    }

    protected function createEmptyRequestDataDto(): AbstractRequestData
    {
        $orderMock = $this->createMock(OrderEntity::class);
        $orderMock->method('getExtension')->willReturn($this->createMock(RatepayOrderDataEntity::class));

        return new OrderOperationData(Context::createDefaultContext(), $orderMock, '', [], false);
    }
}
