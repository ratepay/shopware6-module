<?php

namespace Ratepay\RatepayPayments\Components\Checkout;

use RatePAY\Service\DeviceFingerprint;
use Ratepay\RatepayPayments\Components\DeviceFingerprint\DfpService;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutEventListener implements EventSubscriberInterface
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
            CheckoutConfirmPageLoadedEvent::class => 'addRatepayTemplateData'
        ];
    }

    /**
     * @param StorefrontRenderEvent $event
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @codeCoverageIgnore
     */
    public function addRatepayTemplateData(CheckoutConfirmPageLoadedEvent $event): void
    {
        $config = $this->getPluginConfiguration($event->getSalesChannelContext());

        if (!$config['ratepayDevicefingerprintingButton']) {
            return;
        }

        if ($this->dfpService->isDfpIdAlreadyGenerated() == false) {
            $dfpHelper = new DeviceFingerprint($config['ratepayDevicefingerprintingSnippetId']);
            $event->setParameter('dpf', str_replace('\"', '"', $dfpHelper->getDeviceIdentSnippet($this->dfpService->getDfpId())));
        }

    }


    protected function getPluginConfiguration(?SalesChannelContext $context): array
    {
        return $this->systemConfigService->get( 'RatepayPayments.config', $context ? $context->getSalesChannel()->getId() : null);
    }

}
