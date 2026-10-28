<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Subscriber;

use Shopware\Core\Framework\Routing\Event\SalesChannelContextResolvedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ContextSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SalesChannelContextResolvedEvent::class => 'onContextResolved',
        ];
    }

    public function onContextResolved(SalesChannelContextResolvedEvent $event): void
    {
        // Because context switches during normal shopware operations lead to losing the customer's locale, we save the locale in a request attribute
        if (!$this->requestStack->getMainRequest()->attributes->has('ratepay_saved_locale')) {
            $this->requestStack->getMainRequest()->attributes->set('ratepay_saved_locale', $event->getSalesChannelContext()->getLanguageInfo()->localeCode);
        }
    }
}
