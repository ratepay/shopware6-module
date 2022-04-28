<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\RatepayApi\Service\Request;

use RatePAY\Model\Request\SubModel\Content\ShoppingBasket;
use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\AbstractRequest;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Service\Request\PaymentRequestServiceMock;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Service\Request\PaymentReturnServiceMock;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;

class PaymentReturnServiceTest extends AbstractRequestService
{
    use KernelTestBehaviour;

    public function testGetRequestContent(): void
    {
        /** @var PaymentRequestServiceMock $service */
        $service = $this->getServiceMock();

        $requestData = $this->createEmptyRequestDataDto();

        $content = $service->getRequestContent($requestData);

        self::assertNotNull($content->getShoppingBasket(), 'basket must be set for PaymentReturn');
        self::assertNull($content->getCustomer(), 'customer is not required for PaymentReturn');
        self::assertNull($content->getPayment(), 'payment is not required for PaymentReturn');
        self::assertNull($content->getInvoicing(), 'invoice is not required for PaymentReturn');
    }

    protected function getServiceMock(): ?AbstractRequest
    {
        $headFactory = $this->createMock(HeadFactory::class);
        $headFactory->method('getData')->willReturn(new Head());

        $shoppingBasketFactory = $this->createMock(ShoppingBasketFactory::class);
        $shoppingBasketFactory->method('getData')->willReturn(new ShoppingBasket());

        return new PaymentReturnServiceMock($headFactory, $shoppingBasketFactory);
    }

    protected function createEmptyRequestDataDto(): AbstractRequestData
    {
        $orderMock = $this->createMock(OrderEntity::class);
        $orderMock->method('getExtension')->willReturn($this->createMock(RatepayOrderDataEntity::class));

        return new OrderOperationData(Context::createDefaultContext(), $orderMock, '', [], false);
    }
}
