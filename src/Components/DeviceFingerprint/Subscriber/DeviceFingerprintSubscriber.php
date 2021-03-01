<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\DeviceFingerprint\Subscriber;

use RatePAY\Model\Request\SubModel\Head;
use RatePAY\Model\Request\SubModel\Head\CustomerDevice;
use Ratepay\RpayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RpayPayments\Components\DeviceFingerprint\DfpService;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\AbstractPaymentEvent;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentFailedEvent;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentSuccessfulEvent;
use Ratepay\RpayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RpayPayments\Components\RatepayApi\Event\BuildEvent;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use RatePAY\Service\DeviceFingerprint;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPageLoadedEvent;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Storefront\Page\PageLoadedEvent;
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
    ) {
        $this->dfpService = $dfpService;
        $this->configService = $configService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => ['addRatepayTemplateData', 300],
            AccountEditOrderPageLoadedEvent::class => ['addRatepayTemplateData', 300],
            PaymentSuccessfulEvent::class => 'onOrderComplete',
            PaymentFailedEvent::class => 'onOrderComplete',
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

    public function onOrderComplete(AbstractPaymentEvent $event): void
    {
        $this->dfpService->deleteToken();
    }

    public function addRatepayTemplateData(PageLoadedEvent $event): void
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
