<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use RatePAY\Model\Request\SubModel\Content\Invoicing;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Shopware\Core\Checkout\Document\DocumentEntity;

/**
 * @method getData(AbstractRequestData $requestData) : ?Head
 */
class InvoiceFactory extends AbstractFactory
{
    protected function isSupported(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof OrderOperationData;
    }

    protected function _getData(AbstractRequestData $requestData): ?object
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
