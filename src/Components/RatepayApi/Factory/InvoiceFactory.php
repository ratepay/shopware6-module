<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Factory;


use DateTimeInterface;
use Shopware\Core\Checkout\Order\OrderEntity;

class InvoiceFactory
{

    public function getData(OrderEntity $order)
    {
        return null; //TODO


        $documentModel = $this->modelManager->getRepository(Document::class); //TODO DI
        $document = $documentModel->findOneBy(['orderId' => $order->getId(), 'type' => 1]);
        if ($document !== null) {
            /** @var DateTimeInterface $dateObject */
            $dateObject = $document->getDate();
            $currentDate = $dateObject->format('Y-m-d');
            $currentTime = $dateObject->format('H:i:s');
            $currentDateTime = $currentDate . 'T' . $currentTime;

            return [
                'InvoiceId' => $document->getDocumentId(),
                'InvoiceDate' => $currentDateTime,
                'DeliveryDate' => $currentDateTime
            ];
        }
        return null;
    }
}
