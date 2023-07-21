<?php

declare(strict_types=1);

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
use Ratepay\RpayPayments\Components\OrderManagement\Exception\OrderLineItemDeleteRestrictionException;
use Ratepay\RpayPayments\Components\OrderManagement\Exception\RatepayOrderDataDeleteRestrictionException;
use Ratepay\RpayPayments\Components\OrderManagement\Exception\RatepayOrderLineItemsDataDeleteRestrictionException;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\BeforeDeleteEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PreventOrderDeletion implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $ratepayOrderDataRepository,
        private readonly EntityRepository $ratepayOrderLineItemDataRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeDeleteEvent::class => 'preventOrderDeletion',
        ];
    }

    public function preventOrderDeletion(BeforeDeleteEvent $event): void
    {
        $this->preventDeleteOrder($event);
        $this->preventDeleteLineItem($event);
    }

    private function preventDeleteOrder(BeforeDeleteEvent $event): void
    {
        // TODO maybe this can be removed in the future. Shopware has implemented FRAMEWORK__DELETE_RESTRICTED exception
        // which rejects the delete if order got deleted and the ratepay data still exist.

        // we do not check if the order has a ratepay data, cause the deletion is already prevent by restriction
        // check of shopware.
        // in the past we did a validation if the order has a ratepay-data, but after using the BeforeDeleteEvent it is
        // not required/possible anymore to throw an exception before the restriction check has been executed
        $ids = $event->getIds(RatepayOrderDataDefinition::ENTITY_NAME);
        if ($ids !== []) {
            $criteria = new Criteria($ids);
            $criteria->addAssociation('order.transactions');

            $ratepayOrderDataList = $this->ratepayOrderDataRepository->search($criteria, $event->getContext());
            /** @var RatepayOrderDataEntity $ratepayOrderData */
            foreach ($ratepayOrderDataList->getElements() as $ratepayOrderData) {
                if (MethodHelper::isRatepayOrder($ratepayOrderData->getOrder())) {
                    throw new RatepayOrderDataDeleteRestrictionException();
                }
            }
        }
    }

    private function preventDeleteLineItem(BeforeDeleteEvent $event): void
    {
        if ($event->getIds(RatepayOrderLineItemDataDefinition::ENTITY_NAME) !== []) {
            throw new RatepayOrderLineItemsDataDeleteRestrictionException();
        }

        $lineItemIds = $event->getIds(OrderLineItemDefinition::ENTITY_NAME);

        if ($lineItemIds !== []) {
            $criteria = new Criteria();
            $criteria->addFilter(new EqualsAnyFilter(RatepayOrderLineItemDataEntity::FIELD_ORDER_LINE_ITEM_ID, $lineItemIds));

            if ($this->ratepayOrderLineItemDataRepository->searchIds($criteria, $event->getContext())->getTotal() > 0) {
                throw new OrderLineItemDeleteRestrictionException();
            }
        }
    }
}
