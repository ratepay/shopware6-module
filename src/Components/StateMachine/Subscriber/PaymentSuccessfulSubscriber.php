<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\StateMachine\Subscriber;

use Ratepay\RatepayPayments\Components\PaymentHandler\Event\PaymentSuccessfulEvent;
use Ratepay\RatepayPayments\Components\PluginConfig\Service\ConfigService;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionDefinition;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionActions;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentSuccessfulSubscriber implements EventSubscriberInterface
{

    /**
     * @var StateMachineRegistry
     */
    protected $stateMachineRegistry;

    /**
     * @var ConfigService
     */
    protected $configService;

    public function __construct(StateMachineRegistry $stateMachineRegistry, ConfigService $configService)
    {
        $this->stateMachineRegistry = $stateMachineRegistry;
        $this->configService = $configService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentSuccessfulEvent::class => 'onPaymentSuccessful',
        ];
    }

    public function onPaymentSuccessful(PaymentSuccessfulEvent $event): void
    {
        $order = $event->getOrder();
        $transaction = $order->getTransactions() ? $order->getTransactions()->first() : null;
        if ($transaction) {
            // ToDo: Use state configured in plugin config
            $toState = $this->configService->getPaymentSuccessfulState();
            $this->stateMachineRegistry->transition(
                new Transition(
                    OrderTransactionDefinition::ENTITY_NAME,
                    $transaction->getId(),
                    StateMachineTransitionActions::ACTION_PAID,
                    'stateId'
                ),
                $event->getContext()
            );
        }
    }
}
