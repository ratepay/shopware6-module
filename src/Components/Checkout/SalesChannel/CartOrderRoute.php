<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\SalesChannel;

use Ratepay\RpayPayments\Components\Checkout\Service\DataValidationService;
use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartOrderRoute;
use Shopware\Core\Checkout\Cart\SalesChannel\CartOrderRouteResponse;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CartOrderRoute extends AbstractCartOrderRoute
{
    public function __construct(
        private readonly AbstractCartOrderRoute $innerService,
        private readonly DataValidationService $validatorService
    ) {
    }

    public function getDecorated(): AbstractCartOrderRoute
    {
        return $this->innerService;
    }

    public function order(Cart $cart, SalesChannelContext $context, RequestDataBag $data): CartOrderRouteResponse
    {
        // this is only for api requests.
        // if the order got submitted, it is possible to validate the ratepay data before the order got saved.
        // this validation is optional. The validation will be also processed during handling the payment.

        $paymentHandler = $context->getPaymentMethod()->getHandlerIdentifier();
        if (is_subclass_of($paymentHandler, AbstractPaymentHandler::class) && $data->has('ratepay')) {
            $this->validatorService->validatePaymentData($data, $context);
        }

        return $this->innerService->order($cart, $context, $data);
    }
}
