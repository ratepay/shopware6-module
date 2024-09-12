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
use Shopware\Core\Checkout\Customer\Event\CustomerLogoutEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SessionService $sessionService,
        private readonly RequestStack $requestStack
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CustomerLogoutEvent::class => ['onLogout', -3000], // as late as possible to prioritize thirdparty modules
        ];
    }

    public function onLogout(CustomerLogoutEvent $event): void
    {
        $session = $this->requestStack->getMainRequest()?->getSession();
        if (!$session instanceof SessionInterface) {
            return;
        }

        $this->sessionService->destroy($session);
    }
}
