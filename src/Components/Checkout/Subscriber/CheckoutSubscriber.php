<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Subscriber;

use Ratepay\RatepayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RatepayPayments\Util\MethodHelper;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutSubscriber implements EventSubscriberInterface
{

    /**
     * @var ExtensionService
     */
    protected $extensionService;

    public function __construct(ExtensionService $extensionService)
    {
        $this->extensionService = $extensionService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => ['addRatepayTemplateData', 310],
        ];
    }

    /**
     * @param CheckoutConfirmPageLoadedEvent $event
     * @codeCoverageIgnore
     */
    public function addRatepayTemplateData(CheckoutConfirmPageLoadedEvent $event): void
    {
        $paymentMethod = $event->getSalesChannelContext()->getPaymentMethod();
        if (MethodHelper::isRatepayMethod($paymentMethod->getHandlerIdentifier()) &&
            $event->getPage()->getPaymentMethods()->has($paymentMethod->getId())
        ) {
            $extension = $this->extensionService->buildPaymentDataExtension($event->getSalesChannelContext());
            $event->getPage()->addExtension(ExtensionService::PAYMENT_PAGE_EXTENSION_NAME, $extension);
        }

    }

}
