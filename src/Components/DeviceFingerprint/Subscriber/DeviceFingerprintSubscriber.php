<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\DeviceFingerprint\Subscriber;

use RatePAY\Model\Request\SubModel\Head;
use RatePAY\Model\Request\SubModel\Head\CustomerDevice;
use Ratepay\RatepayPayments\Components\DeviceFingerprint\DfpService;
use Ratepay\RatepayPayments\Components\PaymentHandler\Event\PaymentSuccessfulEvent;
use Ratepay\RatepayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RatepayPayments\Components\RatepayApi\Event\BuildEvent;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use RatePAY\Service\DeviceFingerprint;
use Shopware\Core\Framework\Struct\ArrayStruct;
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
            PaymentSuccessfulEvent::class => 'onPaymentSuccessful',
            PaymentRequestService::EVENT_BUILD_HEAD => 'onPaymentRequest'
        ];
    }

    public function onPaymentRequest(BuildEvent $buildEvent)
    {
        /** @var Head $head */
        $head = $buildEvent->getBuildData();

        if ($this->dfpService->getDfpId()) {
            $head->setCustomerDevice((new CustomerDevice())->setDeviceToken($this->dfpService->getDfpId()));
        }
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
                $extension->set('snippet', $snippet);
                $event->getPage()->addExtension('ratepayDfp', $extension);
            }
        }

    }

}
