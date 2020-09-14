<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Account\Subscriber;

use Ratepay\RatepayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RatepayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RatepayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RatepayPayments\Util\MethodHelper;
use Shopware\Storefront\Event\RouteRequest\HandlePaymentMethodRouteRequestEvent;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccountSubscriber implements EventSubscriberInterface
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
            AccountEditOrderPageLoadedEvent::class => ['addRatepayTemplateData', 310],
            HandlePaymentMethodRouteRequestEvent::class => 'onHandlePaymentMethodRouteRequest',
        ];
    }

    public function addRatepayTemplateData(AccountEditOrderPageLoadedEvent $event): void
    {
        $page = $event->getPage();
        $order = $page->getOrder();
        /** @var RatepayOrderDataEntity $ratepayData */
        $ratepayData = $order->getExtension(OrderExtension::EXTENSION_NAME);
        if (MethodHelper::isRatepayOrder($order) && $ratepayData && $ratepayData->isSuccessful()) {
            // You can't change the payment if it is a ratepay order
            $page->setPaymentChangeable(false);
        } else {
            // Payment change is allowed, prepare ratepay payment data if a ratepay payment method is selected
            $paymentMethod = $event->getSalesChannelContext()->getPaymentMethod();
            if (MethodHelper::isRatepayMethod($paymentMethod->getHandlerIdentifier()) &&
                $event->getPage()->getPaymentMethods()->has($paymentMethod->getId())
            ) {
                $extension = $this->extensionService->buildPaymentDataExtension(
                    $event->getSalesChannelContext(),
                    $page->getOrder()
                );
                $event->getPage()->addExtension(ExtensionService::PAYMENT_PAGE_EXTENSION_NAME, $extension);
            }
        }
    }

    public function onHandlePaymentMethodRouteRequest(HandlePaymentMethodRouteRequestEvent $event): void
    {
        if ($event->getStorefrontRequest()->request->has('ratepay')) {
            $event->getStoreApiRequest()->request->set(
                'ratepay',
                $event->getStorefrontRequest()->request->get('ratepay')
            );
        }
    }
}
