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
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContextSwitchSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SecciService $secciService,
        private readonly EntityRepository $paymentMethodRepository,
        private readonly CartService $cartService,
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SalesChannelContextSwitchEvent::class => 'onContextSwitch'
        ];
    }

    public function onContextSwitch(SalesChannelContextSwitchEvent $event): void
    {
        if ($event->getRequestDataBag()->has(SalesChannelContextService::PAYMENT_METHOD_ID)) {
            $this->triggerSecciDelivery($event);
        }
    }

    private function triggerSecciDelivery(SalesChannelContextSwitchEvent $event): void
    {
        $salesChannelContext = $event->getSalesChannelContext();

        if (($this->systemConfigService->get('RpayPayments.config.ratepaySecciVariant', $salesChannelContext->getSalesChannelId()) ?? 1) != 1) {
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
            $this->secciService->triggerSecciEmail($event->getRequestDataBag()->toRequestDataBag(), $cart, $salesChannelContext);
        }
    }
}
