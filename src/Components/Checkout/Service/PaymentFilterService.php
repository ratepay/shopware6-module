<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Service;

use Ratepay\RpayPayments\Components\Checkout\Event\RatepayPaymentFilterEvent;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\Collection\ProfileConfigMethodCollection;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileByOrderEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileBySalesChannelContext;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodCollection;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PaymentFilterService
{
    private EventDispatcherInterface $eventDispatcher;

    private ProfileByOrderEntity $profileByOrderEntity;

    private ProfileBySalesChannelContext $profileBySalesChannelContext;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ProfileByOrderEntity $profileByOrderEntity,
        ProfileBySalesChannelContext $profileBySalesChannelContext
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->profileByOrderEntity = $profileByOrderEntity;
        $this->profileBySalesChannelContext = $profileBySalesChannelContext;
    }

    public function filterPaymentMethods(PaymentMethodCollection $paymentMethodCollection, SalesChannelContext $salesChannelContext, OrderEntity $order = null): PaymentMethodCollection
    {
        return $paymentMethodCollection->filter(function (PaymentMethodEntity $paymentMethod) use ($salesChannelContext, $order): ?bool {
            if (!MethodHelper::isRatepayMethod($paymentMethod->getHandlerIdentifier())) {
                // payment method is not a ratepay method - so we won't check it.
                return true;
            }

            if (!$order instanceof OrderEntity) {
                $customer = $salesChannelContext->getCustomer();
                if (!$customer instanceof CustomerEntity ||
                    !$customer->getActiveBillingAddress() instanceof CustomerAddressEntity ||
                    !$customer->getActiveShippingAddress() instanceof CustomerAddressEntity
                ) {
                    return false;
                }
            }

            $searchService = $order instanceof OrderEntity ? $this->profileByOrderEntity : $this->profileBySalesChannelContext;
            $profileConfig = $searchService->search(
                $searchService->createSearchObject($order ?? $salesChannelContext)->setPaymentMethodId($paymentMethod->getId())
            )->first();

            if ($profileConfig === null) {
                // no profile config for this sales channel has been found
                return false;
            }

            /** @var ProfileConfigMethodCollection $methodConfigs */
            $methodConfigs = $profileConfig->getPaymentMethodConfigs()->filterByMethod($paymentMethod->getId());
            $methodConfig = $methodConfigs->first();

            if (!$methodConfig instanceof ProfileConfigMethodEntity) {
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
