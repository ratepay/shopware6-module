<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Logging\Services;

use DateTime;
use Exception;
use Monolog\Logger;
use Ratepay\RatepayPayments\Components\PluginConfig\Service\ConfigService;
use RatePAY\RequestBuilder;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

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

    public function logRequest(RequestBuilder $requestBuilder): void
    {
        $requestXml = $requestBuilder->getRequestRaw();
        $responseXml = $requestBuilder->getResponseRaw();

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
        $requestXml = preg_replace(
            "/<bank-account-number>(.*)<\/bank-account-number>/",
            '<bank-account-number>xxxxxxxx</bank-account-number>',
            $requestXml
        );
        $requestXml = preg_replace("/<bank-code>(.*)<\/bank-code>/", '<bank-code>xxxxxxxx</bank-code>', $requestXml);

        try {
            $this->logRepository->create(
                [
                    [
                        'version' => $this->configService->getPluginVersion(),
                        'operation' => $operation,
                        'subOperation' => $operationSubtype,
                        'status' => $operationStatus,
                        'transactionId' => $transactionId,
                        'request' => $requestXml,
                        'response' => $responseXml,
                        'createdAt' => new DateTime()
                    ]
                ],
                Context::createDefaultContext()
            );
        } catch (Exception $exception) {
            $this->logger->error('RatePAY was unable to log request history', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }
}
