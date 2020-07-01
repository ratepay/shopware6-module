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
use Ratepay\RatepayPayments\Components\Logging\Model\HistoryLogEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
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
                    HistoryLogEntity::FIELD_USER => $this->getCurrentAdministrator($context),
                    HistoryLogEntity::FIELD_ORDER_ID => $orderId,
                    HistoryLogEntity::FIELD_EVENT => $message,
                    HistoryLogEntity::FIELD_PRODUCT_NAME => $articleName,
                    HistoryLogEntity::FIELD_PRODUCT_NUMBER => $articleNumber,
                    HistoryLogEntity::FIELD_QTY => $quantity,
                    // ToDo: Check if you can add the correct time for the correct timezone here (or is it a problem of SW6?)
                    HistoryLogEntity::FIELD_CREATED_AT => (new DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
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
