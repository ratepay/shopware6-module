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
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentInitData;
use Ratepay\RpayPayments\Components\RatepayApi\Exception\TransactionIdFetchFailedException;
use Ratepay\RpayPayments\Components\RatepayApi\Model\TransactionIdEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentInitService;
use Ratepay\RpayPayments\Exception\RatepayException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class TransactionIdService
{
    /**
     * @var string
     */
    public const PREFIX_CART = 'cart-';

    /**
     * @var string
     */
    public const PREFIX_ORDER = 'order-';

    private EntityRepository $transactionIdRepository;

    private PaymentInitService $paymentInitService;

    private EntityRepository $profileRepository;

    public function __construct(
        EntityRepository $transactionIdRepository,
        EntityRepository $profileRepository,
        PaymentInitService $paymentInitService
    ) {
        $this->transactionIdRepository = $transactionIdRepository;
        $this->paymentInitService = $paymentInitService;
        $this->profileRepository = $profileRepository;
    }

    public function getStoredTransactionId(SalesChannelContext $salesChannelContext, string $prefix = ''): ?TransactionIdEntity
    {
        return $this->searchTransaction($this->getIdentifier($salesChannelContext, $prefix));
    }

    /**
     * @param ProfileConfigEntity|null|string $profileConfigId if string: uuid of the profile
     * @throws ProfileNotFoundException
     * @throws TransactionIdFetchFailedException
     */
    public function getTransactionId(SalesChannelContext $salesChannelContext, string $prefix = '', $profileConfigId = null): string
    {
        if ($profileConfigId instanceof ProfileConfigEntity) {
            $profileConfig = $profileConfigId;
        } else {
            $profileConfig = $this->profileRepository->search(new Criteria([$profileConfigId]), Context::createDefaultContext())->first();
        }

        if ($profileConfig === null) {
            throw new ProfileNotFoundException();
        }

        $identifier = $this->getIdentifier($salesChannelContext, $prefix);
        $transactionIdEntity = $this->searchTransaction($identifier, $profileConfig);

        $transactionId = null;
        try {
            if (!$transactionIdEntity instanceof TransactionIdEntity) {
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
        } catch (RatepayException $ratepayException) {
            throw new TransactionIdFetchFailedException($ratepayException->getCode(), $ratepayException);
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

        if ($ids->getIds() !== []) {
            $this->transactionIdRepository->delete(array_map(static fn ($id): array => [
                TransactionIdEntity::FIELD_ID => $id,
            ], $ids->getIds()), $context);
        }
    }

    private function searchTransaction(string $identifier, ProfileConfigEntity $profileConfigEntity = null): ?TransactionIdEntity
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter(TransactionIdEntity::FIELD_IDENTIFIER, $identifier))
            ->setLimit(1);

        if ($profileConfigEntity !== null) {
            $criteria->addFilter(new EqualsFilter(TransactionIdEntity::FIELD_PROFILE_ID, $profileConfigEntity->getId()));
        }

        return $this->transactionIdRepository->search($criteria, Context::createDefaultContext())->first();
    }

    private function getIdentifier(SalesChannelContext $salesChannelContext, string $prefix = ''): string
    {
        return $prefix . $salesChannelContext->getToken();
    }
}
