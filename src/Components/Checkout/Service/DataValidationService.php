<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Service;

use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Ratepay\RpayPayments\Util\DataValidationHelper;
use Ratepay\RpayPayments\Util\RequestHelper;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerRegistry;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class DataValidationService
{
    public function __construct(
        private readonly PaymentHandlerRegistry $paymentHandlerRegistry,
        private readonly DataValidator $dataValidator,
        private readonly SecciService $secciService,
        private readonly CartService $cartService,
    ) {
    }

    public function validatePaymentData(DataBag $parameterBag, SalesChannelContext $salesChannelContext, ?OrderEntity $orderEntity = null): void
    {
        if ($orderEntity instanceof OrderEntity) {
            $paymentMethodId = $orderEntity->getTransactions()->last()->getPaymentMethodId();
        } else {
            $paymentMethodId = $salesChannelContext->getPaymentMethod()->getId();
        }

        $paymentHandler = $this->paymentHandlerRegistry->getPaymentMethodHandler($paymentMethodId);

        if (!$paymentHandler instanceof AbstractPaymentHandler) {
            return;
        }

        /** @var DataBag $_parameterBag */
        $_parameterBag = $parameterBag->get(RequestHelper::WRAPPER_KEY, $parameterBag); // paymentDetails is using for pwa request

        if ($orderEntity === null) {
            $this->addSecciRequirement($_parameterBag, get_class($paymentHandler), $paymentMethodId, $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext), $salesChannelContext);
        }

        $validationDefinitions = $paymentHandler->getValidationDefinitions(new RequestDataBag($_parameterBag->all()), $salesChannelContext, $orderEntity);

        $definitions = new DataValidationDefinition();
        DataValidationHelper::addSubConstraints($definitions, $validationDefinitions);
        $dataValidationDefinition = (new DataValidationDefinition())->addSub(RequestHelper::RATEPAY_DATA_KEY, $definitions);

        if ($parameterBag->has(RequestHelper::WRAPPER_KEY)) {
            $dataValidationDefinition = (new DataValidationDefinition())->addSub(RequestHelper::WRAPPER_KEY, $dataValidationDefinition);
        }

        $this->dataValidator->validate($parameterBag->all(), $dataValidationDefinition);
    }

    private function addSecciRequirement(DataBag $dataBag, string $handlerIdentifier, string $paymentMethodId, Cart $cart, SalesChannelContext $salesChannelContext): void
    {
        if ($this->secciService->paymentMethodIdRequiresSecci($handlerIdentifier, $paymentMethodId, $cart, $salesChannelContext)) {
            $dataBag->get(RequestHelper::RATEPAY_DATA_KEY)->set('secciRequired', true);
            $attestationToken = $this->secciService->getAttestationToken(
                $cart->getPrice()->getTotalPrice(),
                $paymentMethodId,
                $salesChannelContext->getCurrencyId(),
                $salesChannelContext->getLanguageId(),
                RequestHelper::getRatepayData($dataBag)->all()
            );
            $dataBag->get(RequestHelper::RATEPAY_DATA_KEY)->set('secci', $attestationToken);
        }
    }
}
