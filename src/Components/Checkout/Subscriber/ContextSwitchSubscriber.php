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
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextSwitchEvent;
use Shopware\Core\System\SalesChannel\Event\SwitchContextEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ContextSwitchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SecciService        $secciService,
        private readonly EntityRepository    $paymentMethodRepository,
        private readonly CartService         $cartService,
        private readonly RequestStack        $requestStack,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SwitchContextEvent::DATABASE_CHECK => 'beforeContextSwitch',
            SalesChannelContextSwitchEvent::class => 'onContextSwitch'
        ];
    }

    public function beforeContextSwitch(SwitchContextEvent $event): void
    {
        if ($event->getRequestData()->has(SalesChannelContextService::PAYMENT_METHOD_ID)) {
            // Save locale for later as the context language resets to default during the context switch operation
            $this->requestStack->getMainRequest()->attributes->set('ratepay_saved_locale', $event->getSalesChannelContext()->getLanguageInfo()->localeCode);
        }
    }

    public function onContextSwitch(SalesChannelContextSwitchEvent $event): void
    {
        $this->maybeResetSecciFailed($event);

        if ($event->getRequestDataBag()->has(SalesChannelContextService::PAYMENT_METHOD_ID)) {
            // Remove SECCI token from sessio
            //$this->secciService->clearAttestationToken();

            $this->triggerSecciDelivery($event);
        }
    }

    private function maybeResetSecciFailed(SalesChannelContextSwitchEvent $event): void
    {
        // Remove Secci failure session data if SECCI-relevant data is changing
        if (
            $event->getRequestDataBag()->has(SalesChannelContextService::PAYMENT_METHOD_ID)
            || $event->getRequestDataBag()->has(SalesChannelContextService::COUNTRY_ID)
            || $event->getRequestDataBag()->has(SalesChannelContextService::CURRENCY_ID)
            || $event->getRequestDataBag()->has(SalesChannelContextService::CUSTOMER_ID)
            || $event->getRequestDataBag()->has(SalesChannelContextService::IMITATING_USER_ID)
            || $event->getRequestDataBag()->has(SalesChannelContextService::LANGUAGE_ID)
        ) {
            $this->requestStack->getSession()->remove('secciFailed');
        }
    }

    private function triggerSecciDelivery(SalesChannelContextSwitchEvent $event): void
    {
        $salesChannelContext = $event->getSalesChannelContext();

        if ($this->secciService->getSecciVariant($salesChannelContext) !== 1) {
            // Only variant 1 triggers automatic SECCI delivery
            return;
        }

        $paymentMethodId = $event->getRequestDataBag()->get(SalesChannelContextService::PAYMENT_METHOD_ID);
        if (!$paymentMethodId) {
            return;
        }

        $paymentMethod = $this->paymentMethodRepository->search(new Criteria([$paymentMethodId]), $salesChannelContext->getContext())->getEntities()->first();
        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        if ($paymentMethod && $this->secciService->paymentMethodRequiresSecci($paymentMethod, $cart, $salesChannelContext)) {
            $result = $this->secciService->triggerSecciEmail($event->getRequestDataBag()->toRequestDataBag(), $cart, $salesChannelContext);
            if (!$result) {
                $this->requestStack->getSession()->set('secciFailed', true);
            }
        }
    }
}
