<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Tests\Mock\Repository;


use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Write\CloneBehavior;


class PaymentMethodRepositoryMock implements EntityRepositoryInterface
{

    /**
     * @var PaymentMethodEntity[]
     */
    private $methods;

    /**
     * @param PaymentMethodEntity[] $methods
     */
    public function __construct(array $methods)
    {
        $this->methods = $methods;
    }

    public function getDefinition(): EntityDefinition
    {
    }

    public function aggregate(Criteria $criteria, Context $context): AggregationResultCollection
    {
    }

    public function searchIds(Criteria $criteria, Context $context): IdSearchResult
    {
        $results = [];
        if(count($criteria->getIds())) {
            foreach ($criteria->getIds() as $id) {
                if (isset($this->methods[$id])) {
                    $results[$id] = $this->methods[$id];
                }
            }
        } else {
            $results = $this->methods;
        }
        return new IdSearchResult(count($results), $results, $criteria, $context);
    }

    /**
     * @param CloneBehavior|null $behavior - @deprecated tag:v6.4.0 - Will be implemented in 6.4.0
     */
    public function clone(string $id, Context $context, ?string $newId = null): EntityWrittenContainerEvent
    {
    }

    public function search(Criteria $criteria, Context $context): EntitySearchResult
    {
        $results = [];
        if(count($criteria->getIds())) {
            foreach ($criteria->getIds() as $id) {
                if (isset($this->methods[$id])) {
                    $results[$id] = $this->methods[$id];
                }
            }
        } else {
            $results = $this->methods;
        }
        return new EntitySearchResult(count($results), new EntityCollection($results), null, $criteria, $context);
    }

    public function update(array $data, Context $context): EntityWrittenContainerEvent
    {
    }

    public function upsert(array $data, Context $context): EntityWrittenContainerEvent
    {
    }

    public function create(array $data, Context $context): EntityWrittenContainerEvent
    {
    }

    public function delete(array $data, Context $context): EntityWrittenContainerEvent
    {
    }

    public function createVersion(string $id, Context $context, ?string $name = null, ?string $versionId = null): string
    {
    }

    public function merge(string $versionId, Context $context): void
    {
    }
}
