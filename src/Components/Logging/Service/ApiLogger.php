<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\Logging\Model\ApiRequestLogEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use SimpleXMLElement;

class ApiLogger
{
    public function __construct(
        private readonly EntityRepository $logRepository,
        private readonly LoggerInterface $logger,
        private readonly string $pluginVersion
    ) {
    }

    public function logRequest(RequestDoneEvent $requestDoneEvent): void
    {
        $requestBuilder = $requestDoneEvent->getRequestBuilder();
        $requestXml = $requestBuilder->getRequestRaw();
        $responseXml = $requestBuilder->getResponseRaw();

        $additionalData = [];

        $requestData = $requestDoneEvent->getRequestData();

        $requestXmlElement = $requestBuilder->getRequestXmlElement();
        $responseXmlElement = $requestBuilder->getResponseXmlElement();

        if (isset($requestXmlElement->head->{'transaction-id'})) {
            $additionalData['transactionId'] = (string) $requestXmlElement->head->{'transaction-id'};
        } elseif (isset($responseXmlElement->head->{'transaction-id'})) {
            $additionalData['transactionId'] = (string) $responseXmlElement->head->{'transaction-id'};
        }

        if (property_exists($responseXmlElement->content, 'payment') &&
            property_exists($responseXmlElement->content->payment, 'descriptor') &&
            $responseXmlElement->content->payment->descriptor instanceof SimpleXMLElement
        ) {
            $additionalData['descriptor'] = (string) $responseXmlElement->content->payment->descriptor;
        }

        if ($requestData instanceof OrderOperationData) {
            $order = $requestData->getOrder();
            $billingAddress = $order->getAddresses()->get($order->getBillingAddressId());
            $additionalData['orderId'] = $order->getId();
            $additionalData['orderNumber'] = $order->getOrderNumber();
            $additionalData['firstName'] = $billingAddress->getFirstName();
            $additionalData['lastName'] = $billingAddress->getLastName();
            $additionalData['mail'] = $order->getOrderCustomer()->getEmail();

            $ratepayData = $order->getExtension(OrderExtension::EXTENSION_NAME);
            if ($ratepayData instanceof RatepayOrderDataEntity) {
                $additionalData['descriptor'] ??= $ratepayData->getDescriptor();
            }
        }

        /** @var SimpleXMLElement $operationNode */
        $operationNode = $requestBuilder->getRequestXmlElement()->head->operation;
        $operationSubtype = (string) $operationNode->attributes()->subtype;
        $operation = (string) $operationNode;

        // remove sensitive data
        foreach (['securitycode', 'owner', 'iban'] as $key) {
            $requestXml = preg_replace(sprintf('/<%s>(.*)<\/%s>/', $key, $key), sprintf('<%s>xxxxxxxx</%s>', $key, $key), $requestXml);
        }

        $reasonNode = $requestBuilder->getResponseXmlElement()->head->processing->reason;
        $statusNode = $requestBuilder->getResponseXmlElement()->head->processing->status;
        $resultNode = $requestBuilder->getResponseXmlElement()->head->processing->result;

        try {
            $this->logRepository->create(
                [
                    [
                        ApiRequestLogEntity::FIELD_VERSION => $this->pluginVersion,
                        ApiRequestLogEntity::FIELD_OPERATION => $operation,
                        ApiRequestLogEntity::FIELD_SUB_OPERATION => $operationSubtype,

                        ApiRequestLogEntity::FIELD_RESULT_CODE => (string) $resultNode->attributes()->code,
                        ApiRequestLogEntity::FIELD_RESULT_TEXT => (string) $resultNode,
                        ApiRequestLogEntity::FIELD_STATUS_CODE => (string) $statusNode->attributes()->code,
                        ApiRequestLogEntity::FIELD_STATUS_TEXT => (string) $statusNode,
                        ApiRequestLogEntity::FIELD_REASON_CODE => (string) $reasonNode->attributes()->code,
                        ApiRequestLogEntity::FIELD_REASON_TEXT => (string) $reasonNode,

                        ApiRequestLogEntity::FIELD_REQUEST => $requestXml,
                        ApiRequestLogEntity::FIELD_RESPONSE => $responseXml,
                        ApiRequestLogEntity::FIELD_ADDITIONAL_DATA => $additionalData,
                    ],
                ],
                $requestDoneEvent->getRequestData()->getContext()
            );
        } catch (Exception $exception) {
            $this->logger->error('Ratepay was unable to log request history', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }
}
