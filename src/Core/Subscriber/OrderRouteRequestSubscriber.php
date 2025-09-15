<?php declare(strict_types=1);

namespace Ratepay\RpayPayments\Core\Subscriber;

use Ratepay\RpayPayments\Core\Entity\Extension\OrderExtension;
use Shopware\Storefront\Event\RouteRequest\OrderRouteRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderRouteRequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            OrderRouteRequestEvent::class => 'onOrderRouteRequest',
        ];
    }

    public function onOrderRouteRequest(OrderRouteRequestEvent $event): void
    {
        $criteria = $event->getCriteria();
        $criteria->addAssociation(OrderExtension::EXTENSION_NAME);
    }
}
