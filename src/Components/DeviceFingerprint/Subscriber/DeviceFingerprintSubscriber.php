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
use Ratepay\RatepayPayments\Components\Checkout\Service\ExtensionService;
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


    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => ['addRatepayTemplateData', 300],
            PaymentSuccessfulEvent::class => 'onPaymentSuccessful',
            PaymentRequestService::EVENT_BUILD_HEAD => 'onPaymentRequest',
        ];
    }

    public function onPaymentRequest(BuildEvent $buildEvent): void
    {
        /** @var Head $head */
        $head = $buildEvent->getBuildData();

        if ($this->dfpService->getDfpId()) {
            $head->setCustomerDevice((new CustomerDevice())->setDeviceToken($this->dfpService->getDfpId()));
        }
    }

    public function onPaymentSuccessful(PaymentSuccessfulEvent $event): void
    {
        $this->dfpService->deleteToken();
    }

    /**
     * @param CheckoutConfirmPageLoadedEvent $event
     */
    public function addRatepayTemplateData(CheckoutConfirmPageLoadedEvent $event): void
    {
        if ($event->getPage()->hasExtension(ExtensionService::PAYMENT_PAGE_EXTENSION_NAME)) {
            $snippetId = $this->configService->getDeviceFingerprintSnippetId();
            if ($this->dfpService->isDfpIdAlreadyGenerated() === false) {
                $dfpHelper = new DeviceFingerprint($snippetId);
                $snippet = str_replace('\"', '"', $dfpHelper->getDeviceIdentSnippet($this->dfpService->getDfpId()));

                /** @var ArrayStruct $extension */
                $extension = $event->getPage()->getExtension(ExtensionService::PAYMENT_PAGE_EXTENSION_NAME);
                $extension->set('dfp', $snippet);
            }
        }
    }

}
