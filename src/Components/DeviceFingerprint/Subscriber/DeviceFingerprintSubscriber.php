<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\DeviceFingerprint\Subscriber;

use Ratepay\RatepayPayments\Components\DeviceFingerprint\DfpService;
use Ratepay\RatepayPayments\Components\PaymentHandler\Event\PaymentSuccessfulEvent;
use Ratepay\RatepayPayments\Components\PluginConfig\Service\ConfigService;
use RatePAY\Service\DeviceFingerprint;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DeviceFingerprintSubscriber implements EventSubscriberInterface
{
    /**
     * @var DfpService
     */
    protected $dfpService;

    /**
     * @var ConfigService
     */
    private $configService;

    public function __construct(
        DfpService $dfpService,
        ConfigService $configService
    )
    {
        $this->dfpService = $dfpService;
        $this->configService = $configService;
    }


    public static function getSubscribedEvents()
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => 'addRatepayTemplateData',
            PaymentSuccessfulEvent::class => 'onPaymentSuccessful'
        ];
    }

    public function onPaymentSuccessful(PaymentSuccessfulEvent $event)
    {
        $this->dfpService->deleteToken();
    }

    /**
     * @param CheckoutConfirmPageLoadedEvent $event
     * @codeCoverageIgnore
     */
    public function addRatepayTemplateData(CheckoutConfirmPageLoadedEvent $event): void
    {
        if (strpos($event->getSalesChannelContext()->getPaymentMethod()->getHandlerIdentifier(), 'RatepayPayments') !== false) {
            $snippetId = $this->configService->getDeviceFingerprintSnippetId();
            if ($snippetId == null) {
                return;
            }

            if ($this->dfpService->isDfpIdAlreadyGenerated() == false) {
                $dfpHelper = new DeviceFingerprint($snippetId);
                $snippet = str_replace('\"', '"', $dfpHelper->getDeviceIdentSnippet($this->dfpService->getDfpId()));

                $extension = $event->getPage()->getExtension('ratepay') ?? new ArrayStruct();
                $extension->set('dfp', $snippet);
                $event->getPage()->addExtension('ratepay', $extension);
            }
        }

    }

}
