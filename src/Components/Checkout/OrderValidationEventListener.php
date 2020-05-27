<?php

declare(strict_types=1);

namespace Ratepay\RatepayPayments\Components\Checkout;

use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Validator\Constraints\NotBlank;

class OrderValidationEventListener implements EventSubscriberInterface
{
    /** @var RequestStack */
    private $requestStack;

    /** @var CartService */
    private $cartService;

    public function __construct(
        RequestStack $requestStack,
        CartService $cartService
    ) {
        $this->requestStack  = $requestStack;
        $this->cartService   = $cartService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'framework.validation.order.create' => 'validateOrderData',
        ];
    }

    public function validateOrderData(BuildValidationEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return;
        }

        $context = $this->getContextFromRequest($request);

        $cart = $this->cartService->getCart($context->getToken(), $context);

    }

    private function getContextFromRequest(Request $request): SalesChannelContext
    {
        return $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
    }
}
