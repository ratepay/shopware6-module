<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Subscriber;

use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Ratepay\RpayPayments\Util\DataValidationHelper;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerRegistry;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CheckoutValidationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly PaymentHandlerRegistry $paymentHandlerRegistry
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'framework.validation.order.create' => ['validateOrderData', 10],
        ];
    }

    public function validateOrderData(BuildValidationEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return;
        }

        $salesChannelContext = $this->getSalesContextFromRequest($request);
        $paymentMethod = $salesChannelContext->getPaymentMethod();
        $paymentHandlerIdentifier = $paymentMethod->getHandlerIdentifier();

        if (str_contains($paymentHandlerIdentifier, 'RpayPayments')) {
            /** @var AbstractPaymentHandler $paymentHandler */
            $paymentHandler = $this->paymentHandlerRegistry->getPaymentMethodHandler($paymentMethod->getId());

            $validationDefinitions = $paymentHandler->getValidationDefinitions(new RequestDataBag($request->request->all()), $salesChannelContext);

            $definitions = new DataValidationDefinition();
            DataValidationHelper::addSubConstraints($definitions, $validationDefinitions);
            $event->getDefinition()->addSub('ratepay', $definitions);
        }
    }

    private function getSalesContextFromRequest(Request $request): SalesChannelContext
    {
        return $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
    }
}
