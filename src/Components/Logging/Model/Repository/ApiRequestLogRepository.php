<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Model\Repository;

use Ratepay\RpayPayments\Components\Logging\Model\ApiRequestLogEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;

class ApiRequestLogRepository implements EntityRepositoryInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $innerRepo;

    public function __construct(
        EntityRepositoryInterface $innerRepo
    ) {
        $this->innerRepo = $innerRepo;
    }

    public function search(Criteria $criteria, Context $context): EntitySearchResult
    {
        if ($criteria->getTerm()) {
            $criteria->addFilter(new ContainsFilter(ApiRequestLogEntity::FIELD_ADDITIONAL_DATA, $criteria->getTerm()));
            $criteria->setTerm(null);
            $criteria->addSorting(new FieldSorting(ApiRequestLogEntity::FIELD_CREATED_AT, FieldSorting::DESCENDING));
        }

        return $this->innerRepo->search($criteria, $context);
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

    public function clone(string $id, Context $context, ?string $newId = null): EntityWrittenContainerEvent
    {
        return $this->innerRepo->clone($id, $context, $newId);
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

    public function delete(array $ids, Context $context): EntityWrittenContainerEvent
    {
        return $this->innerRepo->delete($ids, $context);
    }
}
