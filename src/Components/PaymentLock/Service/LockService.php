<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentLock\Service;


use Ratepay\RpayPayments\Components\PaymentLock\Model\PaymentLockEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;

class LockService
{

    /**
     * @var EntityRepositoryInterface
     */
    private $paymentLockRepository;

    public function __construct(EntityRepositoryInterface $paymentLockRepository)
    {
        $this->paymentLockRepository = $paymentLockRepository;
    }

    public function lockPaymentMethod(string $paymentMethodId, string $customerId, Context $context)
    {
        $lockedUntil = new \DateTime('+48 hours');
        $lockedUntil = $lockedUntil->format('Y-m-d H:i:s');

        $this->paymentLockRepository->upsert([
            [
                PaymentLockEntity::FIELD_CUSTOMER_ID => $customerId,
                PaymentLockEntity::FIELD_PAYMENT_METHOD_ID => $paymentMethodId,
                PaymentLockEntity::FIELD_LOCKED_UNTIL => $lockedUntil
            ]
        ], $context);
    }

    public function isPaymentLocked(string $paymentMethodId, string $customerId, Context $context): bool
    {
        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter(PaymentLockEntity::FIELD_CUSTOMER_ID, $customerId));
        $criteria->addFilter(new EqualsFilter(PaymentLockEntity::FIELD_PAYMENT_METHOD_ID, $paymentMethodId));
        $criteria->addFilter(new RangeFilter(PaymentLockEntity::FIELD_LOCKED_UNTIL, [RangeFilter::GT => $now]));
        $ids = $this->paymentLockRepository->searchIds($criteria, $context);

        return $ids->getTotal() > 0;
    }
}
