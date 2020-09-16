<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\OrderManagement\Subscriber;

use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LineItemsListener implements EventSubscriberInterface
{
    /**
     * @var EntityRepositoryInterface
     */
    private $lineItemRepository;

    public function __construct(
        EntityRepositoryInterface $lineItemRepository
    ) {
        $this->lineItemRepository = $lineItemRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            'order_line_item.written' => 'onLineItemWritten',
        ];
    }

    public function onLineItemWritten(EntityWrittenEvent $event)
    {
        return;
        $context = Context::createDefaultContext();
        $lineItems = $this->lineItemRepository->search(new Criteria($event->getIds()), $context);
        $data = [];
        /** @var OrderLineItemEntity $item */
        foreach ($lineItems as $item) {
            if (!isset($item->getCustomFields()['ratepay'])) {
                $data[] = [
                    'id' => $item->getId(),
                    'customFields' => array_merge($item->getCustomFields() ?: [], [
                        'ratepay' => [
                            'delivered' => 0,
                            'canceled' => 0,
                            'returned' => 0,
                        ],
                    ]),
                ];
            }
        }
        $this->lineItemRepository->update($data, $context);
    }
}
