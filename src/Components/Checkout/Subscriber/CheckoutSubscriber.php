<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Subscriber;

use Ratepay\RpayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutSubscriber implements EventSubscriberInterface
{
    protected ExtensionService $extensionService;

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
     * @codeCoverageIgnore
     */
    public function addRatepayTemplateData(CheckoutConfirmPageLoadedEvent $event): void
    {
        $paymentMethod = $event->getSalesChannelContext()->getPaymentMethod();
        if (MethodHelper::isRatepayMethod($paymentMethod->getHandlerIdentifier()) &&
            $event->getPage()->getPaymentMethods()->has($paymentMethod->getId())
        ) {
            $extension = $event->getPage()->getExtension(ExtensionService::PAYMENT_PAGE_EXTENSION_NAME) ?? new ArrayStruct();
            $paymentDataExtension = $this->extensionService->buildPaymentDataExtension($event->getSalesChannelContext(), null, $event->getRequest());
            if ($paymentDataExtension instanceof ArrayStruct) {
                $extension->assign($paymentDataExtension->getVars());
            }

            $event->getPage()->addExtension(ExtensionService::PAYMENT_PAGE_EXTENSION_NAME, $extension);
        }
    }
}
