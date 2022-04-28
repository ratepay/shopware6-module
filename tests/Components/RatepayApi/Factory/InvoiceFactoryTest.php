<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Tests\Components\RatepayApi\Factory;

use DateTime;
use PHPUnit\Framework\TestCase;
use RatePAY\Model\Request\SubModel\Content\Invoicing;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Tests\Mock\RatepayApi\Factory\Mock;
use Shopware\Core\Checkout\Document\Aggregate\DocumentType\DocumentTypeEntity;
use Shopware\Core\Checkout\Document\DocumentCollection;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;

class InvoiceFactoryTest extends TestCase
{
    use KernelTestBehaviour;

    public function testGetData()
    {
        $factory = Mock::createInvoiceFactory();

        $requestData = $this->createRequestData(true);

        /** @var Invoicing $invoice */
        $invoice = $factory->getData($requestData);

        self::assertInstanceOf(Invoicing::class, $invoice);
        self::assertEquals('2020-10-15T10:50:30', $invoice->getInvoiceDate());
        self::assertEquals($invoice->getDeliveryDate(), $invoice->getInvoiceDate());
        self::assertEquals('123456789', $invoice->getInvoiceId());
    }

    public function testGetDataFail()
    {
        $factory = Mock::createInvoiceFactory();

        $requestData = $this->createRequestData(false);
        $invoice = $factory->getData($requestData);
        self::assertNull($invoice);
    }

    private function createRequestData($provideInvoice)
    {
        $order = new OrderEntity();
        $order->setDocuments(new DocumentCollection([]));

        if ($provideInvoice) {
            $document1 = new DocumentEntity();
            $document1->setId(Uuid::randomHex());
            $document1->setDocumentType(new DocumentTypeEntity());
            $document1->getDocumentType()->setTechnicalName('invoice');
            $document1->setCreatedAt((new DateTime())->setDate(2020, 10, 15)->setTime(10, 50, 30));
            $document1->setConfig(['documentNumber' => '123456789']);
            $order->getDocuments()->add($document1);
        }

        $document2 = new DocumentEntity();
        $document2->setId(Uuid::randomHex());
        $document2->setDocumentType(new DocumentTypeEntity());
        $document2->getDocumentType()->setTechnicalName('something-else');
        $order->getDocuments()->add($document2);

        return new OrderOperationData(Context::createDefaultContext(), $order, '');
    }
}
