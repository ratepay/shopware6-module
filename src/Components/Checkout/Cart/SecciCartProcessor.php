<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Cart;

use Ratepay\RpayPayments\Components\Checkout\Cart\Error\SecciExpiredError;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\SalesChannel\AbstractContextSwitchRoute;
use Shopware\Core\System\SalesChannel\SalesChannel\ContextSwitchRoute;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

class SecciCartProcessor implements CartProcessorInterface
{
    public function __construct(
        #[Autowire(service: ContextSwitchRoute::class)]
        private readonly AbstractContextSwitchRoute $contextSwitchRoute,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        if (
            $this->requestStack->getMainRequest()?->request->has(SalesChannelContextService::PAYMENT_METHOD_ID)
            || $this->requestStack->getMainRequest()->query->has(SalesChannelContextService::PAYMENT_METHOD_ID)
        ) {
            // Payment method is being changed, do not interfere
            return;
        }

        if ($toCalculate->getErrors()->has(SecciExpiredError::KEY)) {
            // Switch payment method
            $defaultPaymentMethodId = $context->getSalesChannel()->getPaymentMethodId();
            if ($context->getPaymentMethod()->getId() !== $defaultPaymentMethodId) {
                $this->contextSwitchRoute->switchContext(
                    new RequestDataBag([
                        SalesChannelContextService::PAYMENT_METHOD_ID => $defaultPaymentMethodId,
                    ]),
                    $context,
                );
            }
        }
    }
}
