<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service;

use RatePAY\Model\Response\PaymentInit;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentInitData;
use Ratepay\RpayPayments\Components\RatepayApi\Exception\TransactionIdFetchFailedException;
use Ratepay\RpayPayments\Components\RatepayApi\Model\TransactionIdEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentInitService;
use Ratepay\RpayPayments\Exception\RatepayException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class TransactionIdService
{
    /**
     * @var EntityRepositoryInterface
     */
    private $transactionIdRepository;

    /**
     * @var PaymentInitService
     */
    private $paymentInitService;

    public function __construct(
        EntityRepositoryInterface $transactionIdRepository,
        PaymentInitService $paymentInitService
    ) {
        $this->transactionIdRepository = $transactionIdRepository;
        $this->paymentInitService = $paymentInitService;
    }

    /**
     * @throws TransactionIdFetchFailedException
     */
    public function getTransactionId(SalesChannelContext $salesChannelContext): string
    {
        $identifier = $salesChannelContext->getToken();
        $transactionIdEntity = $this->findByIdentifier($identifier, $salesChannelContext->getContext());

        $transactionId = null;
        try {
            if ($transactionIdEntity === null) {
                /** @var PaymentInit $paymentInitResponse */
                $paymentInitResponse = $this->paymentInitService->doRequest(new PaymentInitData($salesChannelContext));
                if ($paymentInitResponse->isSuccessful()) {
                    $this->transactionIdRepository->upsert([
                        [
                            TransactionIdEntity::FIELD_IDENTIFIER => $identifier,
                            TransactionIdEntity::FIELD_TRANSACTION_ID => $paymentInitResponse->getTransactionId(),
                        ],
                    ], $salesChannelContext->getContext());

                    $transactionId = $paymentInitResponse->getTransactionId();
                }
            } else {
                $transactionId = $transactionIdEntity->getTransactionId();
            }
        } catch (RatepayException $exception) {
            throw new TransactionIdFetchFailedException($exception->getCode(), $exception);
        }

        if ($transactionId) {
            return $transactionId;
        }
        throw new TransactionIdFetchFailedException();
    }

    protected function findByIdentifier($identifier, Context $context): ?TransactionIdEntity
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter(TransactionIdEntity::FIELD_IDENTIFIER, $identifier))
            ->setLimit(1);

        return $this->transactionIdRepository
            ->search($criteria, $context)
            ->first();
    }

    public function deleteTransactionId(SalesChannelContext $salesChannelContext): void
    {
        $entity = $this->findByIdentifier($salesChannelContext->getToken(), $salesChannelContext->getContext());
        if ($entity) {
            $this->transactionIdRepository->delete([
                [
                    TransactionIdEntity::FIELD_ID => $entity->getId(),
                ],
            ], $salesChannelContext->getContext());
        }
    }
}
