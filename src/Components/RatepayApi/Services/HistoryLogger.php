<?php

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services;

use DateTime;
use Exception;
use Monolog\Logger;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class HistoryLogger
{
    /**
     * @var Logger
     */
    protected $logger;
    /**
     * @var EntityRepositoryInterface
     */
    private $logRepository;

    public function __construct(
        EntityRepositoryInterface $logRepository,
        Logger $logger
    )
    {
        $this->logger = $logger;
        $this->logRepository = $logRepository;
    }

    public function logHistory($orderId, $message, $articleName, $articleNumber, $quantity)
    {
        try {
            $event = $this->logRepository->create([
                [
                    'orderId' => $orderId,
                    'event' => $message,
                    'articlename' => $articleName,
                    'articlenumber' => $articleNumber,
                    'quantity' => $quantity,
                    'created_at' => new DateTime(),
                ]
            ], Context::createDefaultContext());
        } catch (Exception $exception) {
            $this->logger->error('RatePAY was unable to log order history: ' . $exception->getMessage());
        }
    }
}
