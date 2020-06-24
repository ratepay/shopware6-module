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
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class HistoryLogger
{
    /**
     * @var EntityRepositoryInterface
     */
    protected $logRepository;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        EntityRepositoryInterface $logRepository,
        Logger $logger
    )
    {
        $this->logRepository = $logRepository;
        $this->logger = $logger;
    }

    public function logHistory($orderId, $message, $articleName, $articleNumber, $quantity): void
    {
        try {
            $this->logRepository->create([
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
            $this->logger->error('RatePAY was unable to log order history', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }
}
