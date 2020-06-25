<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Factory;


use RatePAY\Model\Request\SubModel\Content\Invoicing;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Shopware\Core\Checkout\Document\DocumentEntity;

class InvoiceFactory extends AbstractFactory
{

    protected function _getData(IRequestData $requestData): ?object
    {
        /** @var OrderOperationData $requestData */

        $order = $requestData->getOrder();
        $documents = $order->getDocuments()->filter(function (DocumentEntity $documentEntity) {
            return $documentEntity->getDocumentType()->getTechnicalName() === 'invoice';
        });

        $invoiceObject = null;

        if ($invoice = $documents->first()) {
            $dateObject = $invoice->getCreatedAt();
            $currentDate = $dateObject->format('Y-m-d');
            $currentTime = $dateObject->format('H:i:s');
            $currentDateTime = $currentDate . 'T' . $currentTime;

            return (new Invoicing())
                ->setInvoiceId($invoice->getConfig()['documentNumber'])
                ->setInvoiceDate($currentDateTime)
                ->setDeliveryDate($currentDateTime);
        }
        return null;
    }
}
