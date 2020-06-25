<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Logging\Service;

use DateTime;
use Exception;
use Monolog\Logger;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\User\UserEntity;

class HistoryLogger
{
    /**
     * @var EntityRepositoryInterface
     */
    protected $logRepository;

    /**
     * @var EntityRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        EntityRepositoryInterface $logRepository,
        EntityRepositoryInterface $userRepository,
        Logger $logger
    )
    {
        $this->logRepository = $logRepository;
        $this->userRepository = $userRepository;
        $this->logger = $logger;
    }

    public function logHistory(
        Context $context,
        string $orderId,
        string $message,
        string $articleName,
        string $articleNumber,
        int $quantity
    ): void
    {
        try {
            $this->logRepository->create([
                [
                    'user' => $this->getCurrentAdministrator($context),
                    'orderId' => $orderId,
                    'event' => $message,
                    'articlename' => $articleName,
                    'articlenumber' => $articleNumber,
                    'quantity' => $quantity,
                    // ToDo: Check if you can add the correct time for the correct timezone here (or is it a problem of SW6?)
                    'created_at' => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            ], $context);
        } catch (Exception $exception) {
            $this->logger->error('RatePAY was unable to log order history', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }

    protected function getCurrentAdministrator(Context $context): string
    {
        $user = '';
        $contextSource = $context->getSource();
        $userId = $contextSource instanceof AdminApiSource ? $contextSource->getUserId() : null;
        if ($userId !== null) {
            /** @var UserEntity $userEntity */
            $userEntity = $this->userRepository->search(new Criteria([$userId]), $context)->first();
            if ($userEntity->getFirstName() && $userEntity->getLastName()) {
                $user = $userEntity->getFirstName() . ' ' . $userEntity->getLastName();
            } else {
                $user = $userEntity->getUsername();
            }
        }

        return $user;
    }
}
