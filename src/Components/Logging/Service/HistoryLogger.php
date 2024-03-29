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
use Ratepay\RpayPayments\Components\Logging\Model\HistoryLogEntity;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\User\UserEntity;

class HistoryLogger
{
    public function __construct(
        private readonly EntityRepository $logRepository,
        private readonly EntityRepository $userRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function logHistory(
        Context $context,
        string $orderId,
        string $message,
        string $articleName,
        string $articleNumber,
        int $quantity
    ): void {
        try {
            $this->logRepository->create([
                [
                    HistoryLogEntity::FIELD_USER => $this->getCurrentAdministrator($context),
                    HistoryLogEntity::FIELD_ORDER_ID => $orderId,
                    HistoryLogEntity::FIELD_EVENT => $message,
                    HistoryLogEntity::FIELD_PRODUCT_NAME => $articleName,
                    HistoryLogEntity::FIELD_PRODUCT_NUMBER => $articleNumber,
                    HistoryLogEntity::FIELD_QTY => $quantity,
                ],
            ], $context);
        } catch (Exception $exception) {
            $this->logger->error('Ratepay was unable to log order history', [
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
