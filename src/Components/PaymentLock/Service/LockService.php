<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentLock\Service;

use DateTime;
use Ratepay\RpayPayments\Components\PaymentLock\Model\PaymentLockEntity;
use Ratepay\RpayPayments\RpayPayments;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;

class LockService
{
    private EntityRepository $paymentLockRepository;

    /**
     * @var EntityRepository
     * the interface has been deprecated, but shopware is using the Interface in a decorator for the repository.
     * so it will crash, if we are only using EntityRepository, cause an object of the decorator got injected into the constructor.
     * After Shopware has removed the decorator, we can replace this by a normal definition
     * TODO remove comment on Shopware Version 6.5.0.0 & readd type hint & change constructor argument type
     */
    private object $paymentMethodRepository;

    public function __construct(
        object $paymentMethodRepository,
        EntityRepository $paymentLockRepository
    ) {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentLockRepository = $paymentLockRepository;
    }

    /**
     * @param string[]|null $paymentMethodIds
     */
    public function lockPaymentMethod(Context $context, string $customerId, ?array $paymentMethodIds): void
    {
        $lockedUntil = new DateTime('+48 hours');
        $lockedUntil = $lockedUntil->format('Y-m-d H:i:s');

        if ($paymentMethodIds === null) {
            // lock all payment methods
            $criteria = new Criteria();
            $criteria->addAssociation('plugin');
            $criteria->addFilter(new EqualsFilter('plugin.baseClass', RpayPayments::class));
            $paymentMethodIds = $this->paymentMethodRepository->searchIds($criteria, Context::createDefaultContext())->getIds();
        }

        $data = [];
        foreach ($paymentMethodIds as $id) {
            $data[] = [
                PaymentLockEntity::FIELD_CUSTOMER_ID => $customerId,
                PaymentLockEntity::FIELD_PAYMENT_METHOD_ID => $id,
                PaymentLockEntity::FIELD_LOCKED_UNTIL => $lockedUntil,
            ];
        }

        $this->paymentLockRepository->upsert($data, $context);
    }

    public function isPaymentLocked(string $paymentMethodId, string $customerId, Context $context): bool
    {
        $now = new DateTime();
        $now = $now->format('Y-m-d H:i:s');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter(PaymentLockEntity::FIELD_CUSTOMER_ID, $customerId));
        $criteria->addFilter(new EqualsFilter(PaymentLockEntity::FIELD_PAYMENT_METHOD_ID, $paymentMethodId));
        $criteria->addFilter(new RangeFilter(PaymentLockEntity::FIELD_LOCKED_UNTIL, [RangeFilter::GT => $now]));

        $ids = $this->paymentLockRepository->searchIds($criteria, $context);

        return $ids->getTotal() > 0;
    }
}
