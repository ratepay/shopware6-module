<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;


use DateTime;
use PayonePayment\Payone\Request\PayolutionInvoicing\PayolutionInvoicingAuthorizeRequest;
use PHPUnit\Framework\TestCase;
use RatePAY\Model\Request\SubModel\Content\Invoicing;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Shopware\Core\Checkout\Document\Aggregate\DocumentType\DocumentTypeEntity;
use Shopware\Core\Checkout\Document\DocumentCollection;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class InvoiceFactoryTest extends TestCase
{

    use KernelTestBehaviour;


    public function testGetData()
    {
        $invoiceFactory = new InvoiceFactory(new EventDispatcher(), new RequestStack());

        $requestData = $this->createRequestData();

        /** @var Invoicing $invoice */
        $invoice = $invoiceFactory->getData($requestData);

        self::assertInstanceOf(Invoicing::class, $invoice);
        self::assertEquals('2020-10-15T10:50:30', $invoice->getInvoiceDate());
        self::assertEquals($invoice->getDeliveryDate(), $invoice->getInvoiceDate());
        self::assertEquals('123456789', $invoice->getInvoiceId());

    }


    private function createRequestData()
    {

        $order = new OrderEntity();

        $document1 = new DocumentEntity();
        $document1->setId(Uuid::randomHex());
        $document1->setDocumentType(new DocumentTypeEntity());
        $document1->getDocumentType()->setTechnicalName('invoice');
        $document1->setCreatedAt((new DateTime())->setDate(2020, 10, 15)->setTime(10, 50, 30));
        $document1->setConfig(['documentNumber' => "123456789"]);

        $document2 = new DocumentEntity();
        $document2->setId(Uuid::randomHex());
        $document2->setDocumentType(new DocumentTypeEntity());
        $document2->getDocumentType()->setTechnicalName('something-else');

        $order->setDocuments(new DocumentCollection([$document1, $document2]));

        return new OrderOperationData($order, '', [], false,);
    }
}
