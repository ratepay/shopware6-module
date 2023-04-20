<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Logging\Subscriber;

use Ratepay\RpayPayments\Components\Logging\Model\ApiRequestLogEntity;
use Ratepay\RpayPayments\Components\Logging\Model\Definition\ApiRequestLogDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntitySearchedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RepositorySubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            EntitySearchedEvent::class => 'addDefaultSortingForLogs',
        ];
    }

    public function addDefaultSortingForLogs(EntitySearchedEvent $event): void
    {
        if ($event->getDefinition()->getEntityName() !== ApiRequestLogDefinition::ENTITY_NAME) {
            return;
        }

        $criteria = $event->getCriteria();

        if ($criteria->getTerm()) {
            $criteria->addFilter(new ContainsFilter(ApiRequestLogEntity::FIELD_ADDITIONAL_DATA, $criteria->getTerm()));
            $criteria->setTerm(null);
            $criteria->addSorting(new FieldSorting(ApiRequestLogEntity::FIELD_CREATED_AT, FieldSorting::DESCENDING));
        }
    }
}
