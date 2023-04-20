<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Subscriber;

use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Service\PaymentQueryValidatorService;
use Ratepay\RpayPayments\Util\RequestHelper;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CheckoutValidationSubscriber implements EventSubscriberInterface
{
    /**
     * @decrecated use PaymentQueryValidatorService::CODE_METHOD_NOT_AVAILABLE
     * @var string
     */
    public const CODE_METHOD_NOT_AVAILABLE = PaymentQueryValidatorService::CODE_METHOD_NOT_AVAILABLE;

    private RequestStack $requestStack;

    private CartService $cartService;

    private DataValidator $dataValidator;

    private PaymentQueryValidatorService $validatorService;

    public function __construct(
        RequestStack $requestStack,
        DataValidator $dataValidator,
        CartService $cartService,
        PaymentQueryValidatorService $validatorService
    ) {
        $this->requestStack = $requestStack;
        $this->cartService = $cartService;
        $this->dataValidator = $dataValidator;
        $this->validatorService = $validatorService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'framework.validation.order.create' => ['validatePaymentQuery', 5],
        ];
    }

    public function validatePaymentQuery(BuildValidationEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        $context = $this->getContextFromRequest($request);
        $paymentHandlerIdentifier = $context->getPaymentMethod()->getHandlerIdentifier();

        if (strpos($paymentHandlerIdentifier, 'RpayPayments') !== false) {
            $ratepayData = RequestHelper::getArray($request, 'ratepay', []);
            // we must validate the data BEFORE we send the request to the gateway.
            // this is not the good way, but we do not have another possibility.
            // we just want to validate the ratepay-data to prevent unexpected behavior of third-party-plugins
            $definition = new DataValidationDefinition();
            $definition->addSub('ratepay', $event->getDefinition()->getSubDefinitions()['ratepay']);
            $this->dataValidator->validate([
                'ratepay' => $ratepayData,
            ], $definition);

            $this->validatorService->validate(
                $this->cartService->getCart($context->getToken(), $context),
                $context,
                $ratepayData['transactionId'] ?? '--',
                new DataBag($request->request->all())
            );
        }
    }

    private function getContextFromRequest(Request $request): SalesChannelContext
    {
        return $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
    }
}
