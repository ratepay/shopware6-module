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
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerRegistry;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\ParameterBag;

class DataValidationService
{
    public function __construct(
        private readonly PaymentHandlerRegistry $paymentHandlerRegistry,
        private readonly DataValidator $dataValidator
    ) {
    }

    public function validatePaymentData(DataBag $parameterBag, SalesChannelContext|OrderEntity $validationScope): void
    {
        if ($validationScope instanceof OrderEntity) {
            $paymentMethodId = $validationScope->getTransactions()->last()->getPaymentMethodId();
        } else {
            $paymentMethodId = $validationScope->getPaymentMethod()->getId();
        }

        $paymentHandler = $this->paymentHandlerRegistry->getPaymentMethodHandler($paymentMethodId);

        if (!$paymentHandler instanceof AbstractPaymentHandler) {
            return;
        }

        /** @var DataBag $_parameterBag */
        $_parameterBag = $parameterBag->get(RequestHelper::WRAPPER_KEY, $parameterBag); // paymentDetails is using for pwa request
        $validationDefinitions = $paymentHandler->getValidationDefinitions(new RequestDataBag($_parameterBag->all()), $validationScope);

        $definitions = new DataValidationDefinition();
        DataValidationHelper::addSubConstraints($definitions, $validationDefinitions);
        $dataValidationDefinition = (new DataValidationDefinition())->addSub(RequestHelper::RATEPAY_DATA_KEY, $definitions);

        if ($parameterBag->has(RequestHelper::WRAPPER_KEY)) {
            $dataValidationDefinition = (new DataValidationDefinition())->addSub(RequestHelper::WRAPPER_KEY, $dataValidationDefinition);
        }

        $this->dataValidator->validate($parameterBag->all(), $dataValidationDefinition);
    }
}
