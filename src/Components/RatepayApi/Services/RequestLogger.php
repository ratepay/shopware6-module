<?php

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services;

use DateTime;
use Exception;
use Monolog\Logger;
use Ratepay\RatepayPayments\Core\PluginConfig\Services\ConfigService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class RequestLogger
{

    /**
     * @var ConfigService
     */
    protected $config;

    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var EntityRepositoryInterface
     */
    private $logRepository;

    public function __construct(
        ConfigService $config,
        EntityRepositoryInterface $logRepository,
        Logger $logger
    )
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->logRepository = $logRepository;
    }

    /**
     * Logs the Request and Response
     *
     * @param string $requestXml
     * @param string $responseXml
     */
    public function logRequest($requestXml, $responseXml)
    {
        preg_match("/<operation.*>(.*)<\/operation>/", $requestXml, $operationMatches);
        $operation = $operationMatches[1];

        preg_match('/<operation subtype=\"(.*)">(.*)<\/operation>/', $requestXml, $operationSubtypeMatches);
        $operationSubtype = isset($operationSubtypeMatches[1]) ? $operationSubtypeMatches[1] : null;

        preg_match("/<transaction-id>(.*)<\/transaction-id>/", $requestXml, $transactionMatches);
        $transactionId = isset($transactionMatches[1]) ? $transactionMatches[1] : null;

        preg_match('/<status code=\"(.*)">(.*)<\/status>/', $responseXml, $operationStatusMatches);
        $operationStatus = isset($operationStatusMatches[1]) ? $operationStatusMatches[1] : null;

        preg_match("/<transaction-id>(.*)<\/transaction-id>/", $responseXml, $transactionMatchesResponse);
        $transactionId = isset($transactionMatchesResponse[1]) ? $transactionMatchesResponse[1] : $transactionId;

        $requestXml = preg_replace("/<owner>(.*)<\/owner>/", '<owner>xxxxxxxx</owner>', $requestXml);
        $requestXml = preg_replace("/<bank-account-number>(.*)<\/bank-account-number>/", '<bank-account-number>xxxxxxxx</bank-account-number>', $requestXml);
        $requestXml = preg_replace("/<bank-code>(.*)<\/bank-code>/", '<bank-code>xxxxxxxx</bank-code>', $requestXml);

        try {
            $event = $this->logRepository->create([
                [
                    'version' => $this->config->getPluginVersion(),
                    'operation' => $operation,
                    'subOperation' => $operationSubtype,
                    'status' => $operationStatus,
                    'transactionId' => $transactionId,
                    'request' => $requestXml,
                    'response' => $responseXml,
                    'createdAt' => new DateTime()
                ]
            ], Context::createDefaultContext());

        } catch (Exception $exception) {
            $this->logger->error('RatePAY was unable to log request history: ' . $exception->getMessage());
        }
    }
}
