<?php


namespace Ratepay\RpayPayments\Components\AdminOrders\Subscriber;


use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class PageSubscriber implements EventSubscriberInterface
{

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $sessionKey;

    public function __construct(SessionInterface $session, string $sessionKey)
    {
        $this->session = $session;
        $this->sessionKey = $sessionKey;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorefrontRenderEvent::class => 'onPage'
        ];
    }

    public function onPage(StorefrontRenderEvent $event): void
    {
        $event->setParameter('ratepayAdminOrderSession', $this->session->get($this->sessionKey) === true);
    }
}