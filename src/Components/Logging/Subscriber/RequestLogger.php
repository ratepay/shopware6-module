<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Logging\Subscriber;

use DateTime;
use Exception;
use Monolog\Logger;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\RequestDoneEvent;
use Ratepay\RatepayPayments\Core\PluginConfig\Services\ConfigService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RequestLogger implements EventSubscriberInterface
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

    public static function getSubscribedEvents()
    {
        return [
            RequestDoneEvent::class => 'logRequest'
        ];
    }

    /**
     * Logs the Request and Response
     *
     * @param string $requestXml
     * @param string $responseXml
     */
    public function logRequest(RequestDoneEvent $event)
    {
        $requestXml = $event->getRequestBuilder()->getRequestRaw();
        $responseXml = $event->getRequestBuilder()->getResponseRaw();

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
