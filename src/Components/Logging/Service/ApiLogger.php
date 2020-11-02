<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Service;

use Exception;
use Monolog\Logger;
use Ratepay\RpayPayments\Components\Logging\Model\ApiRequestLogEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use SimpleXMLElement;

class ApiLogger
{
    /**
     * @var EntityRepositoryInterface
     */
    protected $logRepository;

    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var string
     */
    private $pluginVersion;

    public function __construct(
        EntityRepositoryInterface $logRepository,
        Logger $logger,
        string $pluginVersion
    )
    {
        $this->logRepository = $logRepository;
        $this->logger = $logger;
        $this->pluginVersion = $pluginVersion;
    }

    public function logRequest(RequestDoneEvent $requestDoneEvent): void
    {
        $requestBuilder = $requestDoneEvent->getRequestBuilder();
        $requestXml = $requestBuilder->getRequestRaw();
        $responseXml = $requestBuilder->getResponseRaw();

        $additionalData = [];

        $requestData = $requestDoneEvent->getRequestData();
        if ($requestData instanceof OrderOperationData) {
            $order = $requestData->getOrder();
            $billingAddress = $order->getAddresses()->get($order->getBillingAddressId());
            $additionalData['transactionId'] = (string)$requestBuilder->getRequestXmlElement()->head->{'transaction-id'};
            $additionalData['orderNumber'] = $order->getOrderNumber();
            $additionalData['firstName'] = $billingAddress->getFirstName();
            $additionalData['lastName'] = $billingAddress->getLastName();
            $additionalData['mail'] = $order->getOrderCustomer()->getEmail();
        }

        /** @var SimpleXMLElement $operationNode */
        $operationNode = $requestBuilder->getRequestXmlElement()->head->operation;
        $operationSubtype = (string)$operationNode->attributes()->subtype;
        $operation = (string)$operationNode;

        $reasonNode = $requestBuilder->getResponseXmlElement()->head->processing->reason;
        $resultNode = $requestBuilder->getResponseXmlElement()->head->processing->result;
        $result = (string)$reasonNode;
        if (in_array(((int)$reasonNode->attributes()->code), [303, 700], true) && ((int)$resultNode->attributes()->code) !== 402) {
            $result = (string)$resultNode;
        }

        foreach (['securitycode', 'owner', 'iban'] as $key) {
            $requestXml = preg_replace("/<$key>(.*)<\/$key>/", "<$key>xxxxxxxx</$key>", $requestXml);
        }

        try {
            $this->logRepository->create(
                [
                    [
                        ApiRequestLogEntity::FIELD_VERSION => $this->pluginVersion,
                        ApiRequestLogEntity::FIELD_OPERATION => $operation,
                        ApiRequestLogEntity::FIELD_SUB_OPERATION => $operationSubtype,
                        ApiRequestLogEntity::FIELD_RESULT => $result,
                        ApiRequestLogEntity::FIELD_REQUEST => $requestXml,
                        ApiRequestLogEntity::FIELD_RESPONSE => $responseXml,
                        ApiRequestLogEntity::FIELD_ADDITIONAL_DATA => $additionalData,
                    ],
                ],
                $requestDoneEvent->getContext()
            );
        } catch (Exception $exception) {
            $this->logger->error('Ratepay was unable to log request history', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }
}
