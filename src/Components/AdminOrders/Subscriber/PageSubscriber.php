<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\AdminOrders\Subscriber;

use Ratepay\RpayPayments\Components\AdminOrders\Service\SessionService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SessionService $sessionService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorefrontRenderEvent::class => 'onPage',
        ];
    }

    public function onPage(StorefrontRenderEvent $event): void
    {
        $session = $event->getRequest()->getSession();
        $event->setParameter('ratepayAdminOrderSession', [
            'active' => $this->sessionService->isAdminSession($event->getSalesChannelContext(), $session),
            'canLogout' => $this->sessionService->canLogout($event->getSalesChannelContext(), $session),
            'isLoggedInAsCustomer' => $this->sessionService->isLoggedInAsCustomer($event->getSalesChannelContext(), $session),
        ]);
    }
}
