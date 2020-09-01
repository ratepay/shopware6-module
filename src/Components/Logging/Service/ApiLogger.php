<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Logging\Service;

use Exception;
use Monolog\Logger;
use Ratepay\RatepayPayments\Components\Logging\Model\ApiRequestLogEntity;
use Ratepay\RatepayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use SimpleXMLElement;

class ApiLogger
{
    /**
     * @var EntityRepositoryInterface
     */
    protected $logRepository;

    /**
     * @var ConfigService
     */
    protected $configService;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        EntityRepositoryInterface $logRepository,
        ConfigService $configService,
        Logger $logger
    )
    {
        $this->logRepository = $logRepository;
        $this->configService = $configService;
        $this->logger = $logger;
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

        $requestXml = preg_replace("/<owner>(.*)<\/owner>/", '<owner>xxxxxxxx</owner>', $requestXml);
        $requestXml = preg_replace("/<bank-account-number>(.*)<\/bank-account-number>/", '<bank-account-number>xxxxxxxx</bank-account-number>', $requestXml);
        $requestXml = preg_replace("/<bank-code>(.*)<\/bank-code>/", '<bank-code>xxxxxxxx</bank-code>', $requestXml);

        try {
            $this->logRepository->create(
                [
                    [
                        ApiRequestLogEntity::FIELD_VERSION => $this->configService->getPluginVersion(),
                        ApiRequestLogEntity::FIELD_OPERATION => $operation,
                        ApiRequestLogEntity::FIELD_SUB_OPERATION => $operationSubtype,
                        ApiRequestLogEntity::FIELD_RESULT => $result,
                        ApiRequestLogEntity::FIELD_REQUEST => $requestXml,
                        ApiRequestLogEntity::FIELD_RESPONSE => $responseXml,
                        ApiRequestLogEntity::FIELD_ADDITIONAL_DATA => $additionalData
                    ]
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
