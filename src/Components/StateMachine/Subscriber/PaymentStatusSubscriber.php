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
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;

class PaymentStatusSubscriber extends AbstractOrderStatusSubscriber
{
    public function __construct(
        private readonly StateMachineRegistry $stateMachine,
        private readonly OrderTransactionStateHandler $transactionStateHandler,
        private readonly PluginConfigService $configService
    ) {
    }

    public function onItemsOperationDone(OrderItemOperationDoneEvent $event): void
    {
        if (!$this->configService->isUpdatePaymentStatusOnOrderItemOperation()) {
            return;
        }

        parent::onItemsOperationDone($event);
    }

    protected function onFullCancel(OrderItemOperationDoneEvent $event): void
    {
        $this->transactionStateHandler->cancel(
            $event->getOrderOperationData()->getTransaction()->getId(),
            $event->getContext()
        );
    }

    protected function onFullRefund(OrderItemOperationDoneEvent $event): void
    {
        $this->transactionStateHandler->refund(
            $event->getOrderOperationData()->getTransaction()->getId(),
            $event->getContext()
        );
    }

    protected function onFullDelivery(OrderItemOperationDoneEvent $event): void
    {
        // note: if the transaction has the state "partly_paid" it is not possible to switch to "paid" with the helper-function of the transactionStateHandler.
        // the action-name for partly_paid->paid is `pay` - not `paid`
        // because the transactionStateHandler does not have this transition, we need to do it manually.
        // to keep it "simple" we search for the possible transition. So we can make sure, that if shopware solve this "issue", the code keeps compatible.

        $transitions = $this->stateMachine->getAvailableTransitions(
            OrderTransactionDefinition::ENTITY_NAME,
            $event->getOrderOperationData()->getTransaction()->getId(),
            'stateId',
            $event->getContext()
        );

        foreach ($transitions as $transition) {
            if ($transition->getToStateMachineState()->getTechnicalName() === OrderTransactionStates::STATE_PAID) {
                $this->stateMachine->transition(
                    new Transition(
                        OrderTransactionDefinition::ENTITY_NAME,
                        $event->getOrderOperationData()->getTransaction()->getId(),
                        $transition->getActionName(),
                        'stateId'
                    ),
                    $event->getContext()
                );
                break;
            }
        }
    }

    protected function onPartlyDelivery(OrderItemOperationDoneEvent $event): void
    {
        $this->transactionStateHandler->payPartially(
            $event->getOrderOperationData()->getTransaction()->getId(),
            $event->getContext()
        );
    }

    protected function onPartlyRefund(OrderItemOperationDoneEvent $event): void
    {
        $this->transactionStateHandler->refundPartially(
            $event->getOrderOperationData()->getTransaction()->getId(),
            $event->getContext()
        );
    }
}
