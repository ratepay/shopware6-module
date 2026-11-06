<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Subscriber;

use Ratepay\RpayPayments\Components\Checkout\Service\SecciService;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentSuccessfulEvent;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\SalesChannel\AbstractContextSwitchRoute;
use Shopware\Core\System\SalesChannel\SalesChannel\ContextSwitchRoute;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AfterPaymentSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SecciService $secciService,
        #[Autowire(service: ContextSwitchRoute::class)]
        private readonly AbstractContextSwitchRoute $contextSwitchRoute,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentSuccessfulEvent::class => 'afterPayment',
        ];
    }

    public function afterPayment(PaymentSuccessfulEvent $event): void
    {
        $salesChannelContext = $event->getSalesChannelContext();

        // Clear attestation token so the next checkout can not reuse it
        $this->secciService->clearAttestationToken();

        // If we are using SECCI variant 1, make sure that no SECCI payment method is selected after the order has been placed
        $cart = new Cart('');
        // A bit of an ugly hack, but we only need the total of the placed order so we can find the profile config again
        $cart->setPrice($event->getOrder()->getPrice());
        if (
            $this->secciService->getSecciVariant($salesChannelContext) === 1
            && $this->secciService->paymentMethodRequiresSecci($salesChannelContext->getPaymentMethod(), $cart, $salesChannelContext)
        ) {
            // Reset payment method
            $defaultPaymentMethodId = $event->getSalesChannelContext()->getSalesChannel()->getPaymentMethodId();
            if ($event->getSalesChannelContext()->getPaymentMethod()->getId() !== $defaultPaymentMethodId) {
                $this->contextSwitchRoute->switchContext(
                    new RequestDataBag([
                        SalesChannelContextService::PAYMENT_METHOD_ID => $defaultPaymentMethodId,
                    ]),
                    $event->getSalesChannelContext(),
                );
            }
        }
    }
}
