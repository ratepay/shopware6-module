<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service;

use RatePAY\Model\Response\PaymentInit;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentInitData;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\ProfileConfigService;
use Ratepay\RpayPayments\Components\RatepayApi\Exception\TransactionIdFetchFailedException;
use Ratepay\RpayPayments\Components\RatepayApi\Model\TransactionIdEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentInitService;
use Ratepay\RpayPayments\Exception\RatepayException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
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

    /**
     * @var ProfileConfigService
     */
    private $profileConfigService;

    public function __construct(
        EntityRepositoryInterface $transactionIdRepository,
        ProfileConfigService $profileConfigService,
        PaymentInitService $paymentInitService
    ) {
        $this->transactionIdRepository = $transactionIdRepository;
        $this->paymentInitService = $paymentInitService;
        $this->profileConfigService = $profileConfigService;
    }

    /**
     * @throws TransactionIdFetchFailedException
     */
    public function getTransactionId(SalesChannelContext $salesChannelContext, string $prefix): string
    {
        $profileConfig = $this->profileConfigService->getProfileConfigBySalesChannel($salesChannelContext);
        if ($profileConfig === null) {
            throw new ProfileNotFoundException();
        }

        $identifier = $prefix . $salesChannelContext->getToken();

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter(TransactionIdEntity::FIELD_IDENTIFIER, $identifier))
            ->addFilter(new EqualsFilter(TransactionIdEntity::FIELD_PROFILE_ID, $profileConfig->getId()))
            ->setLimit(1);
        $transactionIdEntity = $this->transactionIdRepository->search($criteria, $salesChannelContext->getContext())->first();

        $transactionId = null;
        try {
            if ($transactionIdEntity === null) {
                /** @var PaymentInit $paymentInitResponse */
                $paymentInitResponse = $this->paymentInitService->doRequest(new PaymentInitData($profileConfig, $salesChannelContext->getContext()));
                if ($paymentInitResponse->isSuccessful()) {
                    $this->transactionIdRepository->upsert([
                        [
                            TransactionIdEntity::FIELD_IDENTIFIER => $identifier,
                            TransactionIdEntity::FIELD_PROFILE_ID => $profileConfig->getId(),
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

    public function deleteTransactionId(string $transactionId, Context $context): void
    {
        // do not delete all transactions for the sales-channel-token. Only for the processed transaction-id.
        $criteria = (new Criteria())
            ->addFilter(new ContainsFilter(TransactionIdEntity::FIELD_TRANSACTION_ID, $transactionId));

        $ids = $this->transactionIdRepository->searchIds($criteria, $context);

        if (count($ids->getIds())) {
            $this->transactionIdRepository->delete(array_map(static function ($id) {
                return [
                    TransactionIdEntity::FIELD_ID => $id,
                ];
            }, $ids->getIds()), $context);
        }
    }
}
