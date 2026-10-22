<?php declare(strict_types=1);

namespace Ratepay\RpayPayments\Components\Checkout\Subscriber;

use Ratepay\RpayPayments\Components\Checkout\Cart\SecciExpiredError;
use Shopware\Core\Checkout\Cart\Extension\CheckoutCartRuleLoaderExtension;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\SalesChannel\AbstractContextSwitchRoute;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CartLoadingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AbstractContextSwitchRoute $contextSwitchRoute,
        private readonly RequestStack               $requestStack,
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutCartRuleLoaderExtension::onPost() => 'afterCartLoaded',
        ];
    }

    public function afterCartLoaded(CheckoutCartRuleLoaderExtension $event): void
    {
        if (
            $this->requestStack->getMainRequest()?->request->has(SalesChannelContextService::PAYMENT_METHOD_ID)
            || $this->requestStack->getMainRequest()->query->has(SalesChannelContextService::PAYMENT_METHOD_ID)
        ) {
            // Payment method is being changed, do not interfere
            return;
        }

        $cart = $event->result()?->getCart();
        if ($cart && $cart->getErrors()->has(SecciExpiredError::KEY)) {
            // Switch payment method
            $defaultPaymentMethodId = $event->getSalesChannelContext()->getSalesChannel()->getPaymentMethodId();
            $this->contextSwitchRoute->switchContext(
                new RequestDataBag([
                    SalesChannelContextService::PAYMENT_METHOD_ID => $defaultPaymentMethodId,
                ]),
                $event->getSalesChannelContext(),
            );
        }
    }
}