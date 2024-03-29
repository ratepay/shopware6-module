<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\AdminOrders\Subscriber;

use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly string $sessionKey
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
        $event->setParameter('ratepayAdminOrderSession', $session->get($this->sessionKey) === true);
    }
}
