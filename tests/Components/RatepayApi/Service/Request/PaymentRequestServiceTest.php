<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\RatepayApi\Service\Request;

use RatePAY\Model\Request\SubModel\Content\Customer;
use RatePAY\Model\Request\SubModel\Content\Payment;
use RatePAY\Model\Request\SubModel\Content\ShoppingBasket;
use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\CustomerFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\PaymentFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\AbstractRequest;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Service\Request\PaymentRequestServiceMock;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;

class PaymentRequestServiceTest extends AbstractRequestService
{
    use KernelTestBehaviour;

    public function testGetRequestContent(): void
    {
        /** @var PaymentRequestServiceMock $service */
        $service = $this->getServiceMock();

        $content = $service->getRequestContent($this->createMock(PaymentRequestData::class));
        self::assertNotNull($content->getShoppingBasket(), 'basket must be set for PaymentRequest');
        self::assertNotNull($content->getCustomer(), 'customer must be set for PaymentRequest');
        self::assertNotNull($content->getPayment(), 'payment must be set for PaymentRequest');
    }

    protected function getServiceMock(): ?AbstractRequest
    {
        $headFactory = $this->createMock(HeadFactory::class);
        $headFactory->method('getData')->willReturn(new Head());

        $shoppingBasketFactory = $this->createMock(ShoppingBasketFactory::class);
        $shoppingBasketFactory->method('getData')->willReturn(new ShoppingBasket());

        $customerFactory = $this->createMock(CustomerFactory::class);
        $customerFactory->method('getData')->willReturn(new Customer());

        $paymentFactory = $this->createMock(PaymentFactory::class);
        $paymentFactory->method('getData')->willReturn(new Payment());

        return new PaymentRequestServiceMock($headFactory, $shoppingBasketFactory, $customerFactory, $paymentFactory);
    }
}
