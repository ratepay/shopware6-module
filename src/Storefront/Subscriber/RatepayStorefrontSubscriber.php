<?php

namespace Ratepay\RatepayPayments\Storefront\Subscriber;

use Ratepay\RatepayPayments\Helper\SessionHelper;
use Ratepay\RatepayPayments\Components\DeviceFingerprint\DfpService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RatepayStorefrontSubscriber implements EventSubscriberInterface
{

    /**
     * @var DfpService
     */
    protected $dfpService;

    /**
     * @var SessionHelper
     */
    private $sessionHelper;


    public function __construct(
        DfpService $dfpService,
        SessionHelper $sessionHelper
    )
    {
        $this->dfpService = $dfpService;
        $this->sessionHelper = $sessionHelper;
    }


    public static function getSubscribedEvents()
    {
        return [
            StorefrontRenderEvent::class => 'onStorefrontRender'
        ];
    }

    /**
     * @param StorefrontRenderEvent $event
     */
    public function onStorefrontRender(StorefrontRenderEvent $event): void
    {
        
    }
}
