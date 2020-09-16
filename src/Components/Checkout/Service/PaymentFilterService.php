<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Service;

use Ratepay\RpayPayments\Components\Checkout\Event\RatepayPaymentFilterEvent;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\Collection\ProfileConfigMethodCollection;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\ProfileConfigService;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodCollection;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PaymentFilterService
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ProfileConfigService
     */
    private $profileConfigService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ProfileConfigService $profileConfigService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->profileConfigService = $profileConfigService;
    }

    public function filterPaymentMethods(PaymentMethodCollection $paymentMethodCollection, SalesChannelContext $salesChannelContext, OrderEntity $order = null): PaymentMethodCollection
    {
        return $paymentMethodCollection->filter(function (PaymentMethodEntity $paymentMethod) use ($salesChannelContext, $order) {
            if (MethodHelper::isRatepayMethod($paymentMethod->getHandlerIdentifier()) === false) {
                // payment method is not a ratepay method - so we won't check it.
                return true;
            }

            if ($order) {
                $profileConfig = $this->profileConfigService->getProfileConfigByOrderEntity(
                    $order,
                    $paymentMethod->getId(),
                    $salesChannelContext->getContext()
                );
            } else {
                $profileConfig = $this->profileConfigService->getProfileConfigBySalesChannel(
                    $salesChannelContext,
                    $paymentMethod->getId()
                );
            }

            if ($profileConfig === null) {
                // no profile config for this sales channel is found
                return false;
            }

            /** @var ProfileConfigMethodCollection $methodConfigs */
            $methodConfigs = $profileConfig->getPaymentMethodConfigs()->filterByMethod($paymentMethod->getId());
            $methodConfig = $methodConfigs->first();

            if ($methodConfig === null) {
                // no profile method config is found
                return null;
            }

            // trigger event to filter the payment methods
            /** @var RatepayPaymentFilterEvent $filterEvent */
            $filterEvent = $this->eventDispatcher->dispatch(new RatepayPaymentFilterEvent(
                $paymentMethod,
                $profileConfig,
                $methodConfig,
                $salesChannelContext,
                $order
            ));

            return $filterEvent->isAvailable();
        });
    }
}
