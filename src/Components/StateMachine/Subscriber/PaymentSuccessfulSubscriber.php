<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\StateMachine\Subscriber;

use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentSuccessfulEvent;
use Ratepay\RpayPayments\Core\PluginConfigService;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentSuccessfulSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly StateMachineRegistry $stateMachineRegistry,
        private readonly PluginConfigService $configService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentSuccessfulEvent::class => ['changeTransactionState', 6000],
        ];
    }

    public function changeTransactionState(PaymentSuccessfulEvent $event): void
    {
        $paymentMethod = $event->getTransaction()->getOrderTransaction()->getPaymentMethod();
        $newState = $this->configService->getPaymentStatusForMethod($paymentMethod);
        if ($newState && $newState !== OrderTransactionStates::STATE_OPEN) {
            $this->stateMachineRegistry->transition(
                new Transition(
                    OrderTransactionDefinition::ENTITY_NAME,
                    $event->getTransaction()->getOrderTransaction()->getId(),
                    $newState,
                    'stateId'
                ),
                $event->getSalesChannelContext()->getContext()
            );
        }
    }
}
