<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Account\Subscriber;

use Ratepay\RatepayPayments\Components\Checkout\Model\Extension\OrderExtension;
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
            AccountEditOrderPageLoadedEvent::class => 'onAccountEditOrderPageLoaded',
            HandlePaymentMethodRouteRequestEvent::class => 'onHandlePaymentMethodRouteRequest',
        ];
    }

    public function onAccountEditOrderPageLoaded(AccountEditOrderPageLoadedEvent $event): void
    {
        $page = $event->getPage();
        if ($page->getOrder()->hasExtension(OrderExtension::EXTENSION_NAME)) {
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
                $event->getPage()->addExtension('ratepay', $extension);
                // ToDo: Beim Installment Calculator wird auf den Warenkorb zugegriffen. Dort muss
                // für diesen Fall auf die Bestellpositionen zugegriffen werden
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
