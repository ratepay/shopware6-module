<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Model\Repository;

use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderLineItemDataEntity;
use Ratepay\RpayPayments\Components\OrderManagement\Exception\OrderLineItemDeleteRestrictionException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Write\CloneBehavior;

class OrderLineItemRepositoryDecorator implements EntityRepositoryInterface
{
    private EntityRepositoryInterface $innerRepo;

    private EntityRepositoryInterface $ratepayOrderLineItemDataRepository;

    public function __construct(
        EntityRepositoryInterface $innerRepo,
        EntityRepositoryInterface $ratepayOrderLineItemDataRepository
    ) {
        $this->innerRepo = $innerRepo;
        $this->ratepayOrderLineItemDataRepository = $ratepayOrderLineItemDataRepository;
    }

    public function delete(array $ids, Context $context): EntityWrittenContainerEvent
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter(RatepayOrderLineItemDataEntity::FIELD_ORDER_LINE_ITEM_ID, array_column($ids, 'id')));
        $ratepayLineItems = $this->ratepayOrderLineItemDataRepository->searchIds($criteria, $context);

        if (count($ratepayLineItems->getIds()) > 0) {
            throw new OrderLineItemDeleteRestrictionException();
        }

        return $this->innerRepo->delete($ids, $context);
    }

    // Unchanged methods

    public function getDefinition(): EntityDefinition
    {
        return $this->innerRepo->getDefinition();
    }

    public function aggregate(Criteria $criteria, Context $context): AggregationResultCollection
    {
        return $this->innerRepo->aggregate($criteria, $context);
    }

    public function searchIds(Criteria $criteria, Context $context): IdSearchResult
    {
        return $this->innerRepo->searchIds($criteria, $context);
    }

    public function clone(string $id, Context $context, ?string $newId = null, ?CloneBehavior $behavior = null): EntityWrittenContainerEvent
    {
        return $this->innerRepo->clone($id, $context, $newId);
    }

    public function search(Criteria $criteria, Context $context): EntitySearchResult
    {
        return $this->innerRepo->search($criteria, $context);
    }

    public function update(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->innerRepo->update($data, $context);
    }

    public function upsert(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->innerRepo->upsert($data, $context);
    }

    public function create(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->innerRepo->create($data, $context);
    }

    public function createVersion(string $id, Context $context, ?string $name = null, ?string $versionId = null): string
    {
        return $this->innerRepo->createVersion($id, $context, $name, $versionId);
    }

    public function merge(string $versionId, Context $context): void
    {
        $this->innerRepo->merge($versionId, $context);
    }
}
