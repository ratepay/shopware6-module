<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\AdminOrders\Subscriber;

use Ratepay\RpayPayments\Components\ProfileConfig\Event\CreateProfileConfigCriteriaEvent;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ProfileConfigSubscriber implements EventSubscriberInterface
{
    private SessionInterface $session;

    private string $sessionKey;

    public function __construct(SessionInterface $session, string $sessionKey)
    {
        $this->session = $session;
        $this->sessionKey = $sessionKey;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CreateProfileConfigCriteriaEvent::class => 'onLoadConfig',
        ];
    }

    public function onLoadConfig(CreateProfileConfigCriteriaEvent $event): void
    {
        if ($this->session->get($this->sessionKey) === true) {
            $event->getCriteria()->addFilter(new EqualsFilter(ProfileConfigEntity::FIELD_ONLY_ADMIN_ORDERS, true));
        } else {
            $event->getCriteria()->addFilter(new EqualsFilter(ProfileConfigEntity::FIELD_ONLY_ADMIN_ORDERS, false));
        }
    }
}
