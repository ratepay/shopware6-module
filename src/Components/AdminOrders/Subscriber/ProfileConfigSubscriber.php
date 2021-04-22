<?php


namespace Ratepay\RpayPayments\Components\AdminOrders\Subscriber;


use Ratepay\RpayPayments\Components\AdminOrders\Model\Extension\ProfileConfigExtension;
use Ratepay\RpayPayments\Components\ProfileConfig\Event\CreateProfileConfigCriteriaEvent;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ProfileConfigSubscriber implements EventSubscriberInterface
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

    public static function getSubscribedEvents()
    {
        return [
            CreateProfileConfigCriteriaEvent::class => 'onLoadConfig'
        ];
    }

    public function onLoadConfig(CreateProfileConfigCriteriaEvent $event)
    {
        if($this->session->get($this->sessionKey) === true) {
            $event->getCriteria()->addFilter(new EqualsFilter(ProfileConfigEntity::FIELD_ONLY_ADMIN_ORDERS, true));
        } else {
            $event->getCriteria()->addFilter(new EqualsFilter(ProfileConfigEntity::FIELD_ONLY_ADMIN_ORDERS, false));
        }
    }
}
