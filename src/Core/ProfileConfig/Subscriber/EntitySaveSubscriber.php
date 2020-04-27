<?php


namespace RatePay\RatePayPayments\Core\ProfileConfig\Subscriber;


use RatePay\RatePayPayments\Core\ProfileConfig\Service\ProfileConfigService;
use RatePay\RatePayPayments\Core\ProfileConfig\ProfileConfigDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntitySaveSubscriber implements EventSubscriberInterface
{

    /**
     * @var ProfileConfigService
     */
    private $profileConfigService;

    public function __construct(ProfileConfigService $profileConfigService)
    {
        $this->profileConfigService = $profileConfigService;
    }

    public static function getSubscribedEvents()
    {
        return [
            //ProfileConfigDefinition::ENTITY_NAME . '.written' => 'onSave'
        ];
    }

    public function onSave(EntityWrittenEvent $event)
    {
        $this->profileConfigService->refreshProfileConfigs($event->getIds());
    }
}
