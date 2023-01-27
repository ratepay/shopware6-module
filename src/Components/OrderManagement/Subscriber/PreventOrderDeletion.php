<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\OrderManagement\Subscriber;

use Ratepay\RpayPayments\Components\Checkout\Model\Definition\RatepayOrderDataDefinition;
use Ratepay\RpayPayments\Components\Checkout\Model\Definition\RatepayOrderLineItemDataDefinition;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderLineItemDataEntity;
use Ratepay\RpayPayments\Components\OrderManagement\Exception\RatepayOrderDataDeleteRestrictionException;
use Ratepay\RpayPayments\Components\OrderManagement\Exception\OrderLineItemDeleteRestrictionException;
use Ratepay\RpayPayments\Components\OrderManagement\Exception\RatepayOrderLineItemsDataDeleteRestrictionException;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\BeforeDeleteEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PreventOrderDeletion implements EventSubscriberInterface
{

    private EntityRepository $ratepayOrderLineItemDataRepository;

    public function __construct(
        EntityRepository $ratepayOrderLineItemDataRepository
    )
    {
        $this->ratepayOrderLineItemDataRepository = $ratepayOrderLineItemDataRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeDeleteEvent::class => 'preventOrderDeletion'
        ];
    }

    public function preventOrderDeletion(BeforeDeleteEvent $event): void
    {
        $this->preventDeleteOrder($event);
        $this->preventDeleteLineItem($event);
    }

    private function preventDeleteOrder(BeforeDeleteEvent $event): void
    {
        // we do not check if the order has a ratepay data, cause the deletion is already prevent by restriction
        // check of shopware.
        // in the past we did a validation if the order has a ratepay-data, but after using the BeforeDeleteEvent it is
        // not required/possible anymore to throw an exception before the restriction check has been executed
        if ($event->getIds(RatepayOrderDataDefinition::ENTITY_NAME) !== []) {
            throw new RatepayOrderDataDeleteRestrictionException();
        }
    }

    private function preventDeleteLineItem(BeforeDeleteEvent $event): void
    {
        if ($event->getIds(RatepayOrderLineItemDataDefinition::ENTITY_NAME) !== []) {
            throw new RatepayOrderLineItemsDataDeleteRestrictionException();
        }

        $lineItemIds = $event->getIds(OrderLineItemDefinition::ENTITY_NAME);

        if ($lineItemIds && count($lineItemIds)) {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsAnyFilter(RatepayOrderLineItemDataEntity::FIELD_ORDER_LINE_ITEM_ID, $lineItemIds));

            if ($this->ratepayOrderLineItemDataRepository->searchIds($criteria, $event->getContext())->getTotal() > 0) {
                throw new OrderLineItemDeleteRestrictionException();
            }
        }
    }
}
