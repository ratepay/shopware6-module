<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\StateMachine\Subscriber;

use Ratepay\RpayPayments\Core\Event\OrderItemOperationDoneEvent;
use Ratepay\RpayPayments\Core\PluginConfigService;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderDelivery\OrderDeliveryEntity;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionActions;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;

class DeliveryStatusSubscriber extends AbstractOrderStatusSubscriber
{
    public function __construct(
        private readonly StateMachineRegistry $stateMachine,
        private readonly PluginConfigService $configService
    ) {
    }

    public function onItemsOperationDone(OrderItemOperationDoneEvent $event): void
    {
        /** @var ArrayStruct|null $ratepayContext */
        $ratepayContext = $event->getContext()->getExtension('ratepay');
        if ($ratepayContext instanceof ArrayStruct && $ratepayContext->get('onTransition') === true) {
            return;
        }

        $delivery = $this->getDelivery($event);

        if (!$this->configService->isUpdateDeliveryStatusOnOrderItemOperation() || !$delivery instanceof OrderDeliveryEntity) {
            return;
        }

        parent::onItemsOperationDone($event);
    }

    protected function onFullCancel(OrderItemOperationDoneEvent $event): void
    {
        $this->doTransition($event, StateMachineTransitionActions::ACTION_CANCEL);
    }

    protected function onFullRefund(OrderItemOperationDoneEvent $event): void
    {
        // we won't save this change, because it could be that the items are only refunded and not returned.
        // $this->doTransition($event, StateMachineTransitionActions::ACTION_RETOUR);
    }

    protected function onFullDelivery(OrderItemOperationDoneEvent $event): void
    {
        $this->doTransition($event, StateMachineTransitionActions::ACTION_SHIP);
    }

    protected function onPartlyDelivery(OrderItemOperationDoneEvent $event): void
    {
        $this->doTransition($event, StateMachineTransitionActions::ACTION_SHIP_PARTIALLY);
    }

    protected function onPartlyRefund(OrderItemOperationDoneEvent $event): void
    {
        // we won't save this change, because it could be that the items are only refunded and not returned.
        // $this->doTransition($event, StateMachineTransitionActions::ACTION_RETOUR_PARTIALLY);
    }

    private function doTransition(OrderItemOperationDoneEvent $event, string $transitionName): void
    {
        /** @var OrderDeliveryEntity $delivery */
        $delivery = $this->getDelivery($event);

        $this->stateMachine->transition(
            new Transition(
                OrderDeliveryDefinition::ENTITY_NAME,
                $delivery->getId(),
                $transitionName,
                'stateId'
            ),
            $event->getContext()
        );
    }

    private function getDelivery(OrderItemOperationDoneEvent $event): ?OrderDeliveryEntity
    {
        return $event->getOrderEntity()->getDeliveries()?->first();
    }
}
