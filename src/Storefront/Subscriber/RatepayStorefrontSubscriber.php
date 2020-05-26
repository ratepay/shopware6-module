<?php

namespace Ratepay\RatepayPayments\Storefront\Subscriber;

use RatePAY\Service\DeviceFingerprint;
use Ratepay\RatepayPayments\Components\DeviceFingerprint\DfpService;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RatepayStorefrontSubscriber implements EventSubscriberInterface
{

    /**
     * @var DfpService
     */
    protected $dfpService;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(
        DfpService $dfpService,
        SystemConfigService $systemConfigService
    )
    {
        $this->dfpService = $dfpService;
        $this->systemConfigService = $systemConfigService;
    }


    public static function getSubscribedEvents()
    {
        return [
            StorefrontRenderEvent::class => 'onStorefrontRender'
        ];
    }

    /**
     * @param StorefrontRenderEvent $event
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @codeCoverageIgnore
     */
    public function onStorefrontRender(StorefrontRenderEvent $event): void
    {
        $config = $this->getPluginConfiguration($event->getSalesChannelContext());

        if (!$config['ratepayDevicefingerprintingButton']) {
            return;
        }

        if (strpos($event->getView(), 'storefront/page/checkout/confirm/index.html.twig') !== false) {
            if ($this->dfpService->isDfpIdAlreadyGenerated() == false) {
                $dfpHelper = new DeviceFingerprint($config['ratepayDevicefingerprintingSnippetId']);
                $event->setParameter('dpf', str_replace('\"', '"', $dfpHelper->getDeviceIdentSnippet($this->dfpService->getDfpId())));
            }
        }
    }


    protected function getPluginConfiguration(?SalesChannelContext $context): array
    {
        return $this->systemConfigService->get( 'RatepayPayments.config', $context ? $context->getSalesChannel()->getId() : null);
    }

}
