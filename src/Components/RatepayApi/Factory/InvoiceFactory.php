<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Factory;


use RatePAY\Model\Request\SubModel\Content\Invoicing;
use Shopware\Core\Checkout\Document\DocumentEntity;
use Shopware\Core\Checkout\Order\OrderEntity;

class InvoiceFactory
{

    public function getData(OrderEntity $order)
    {
        $documents = $order->getDocuments()->filter(function (DocumentEntity $documentEntity) {
            return $documentEntity->getDocumentType()->getTechnicalName() === 'invoice';
        });

        if ($invoice = $documents->first()) {
            $dateObject = $invoice->getCreatedAt();
            $currentDate = $dateObject->format('Y-m-d');
            $currentTime = $dateObject->format('H:i:s');
            $currentDateTime = $currentDate . 'T' . $currentTime;

            return (new Invoicing())
                ->setInvoiceId($invoice->getConfig()['documentNumber'])
                ->setInvoiceDate($currentDateTime)
                ->setDeliveryDate($currentDateTime)
                ;
        }
        return null;
    }
}
