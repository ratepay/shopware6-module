<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Event;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class RatepayPaymentFilterEvent extends Event
{
    private PaymentMethodEntity $paymentMethod;

    private ?SalesChannelContext $salesChannelContext;

    private bool $isAvailable = true;

    private ProfileConfigEntity $profileConfig;

    private ProfileConfigMethodEntity $methodConfig;

    private ?OrderEntity $orderEntity;

    public function __construct(
        PaymentMethodEntity $paymentMethod,
        ProfileConfigEntity $profileConfig,
        ProfileConfigMethodEntity $methodConfig,
        SalesChannelContext $salesChannelContext,
        OrderEntity $orderEntity = null
    ) {
        $this->paymentMethod = $paymentMethod;
        $this->profileConfig = $profileConfig;
        $this->methodConfig = $methodConfig;
        $this->salesChannelContext = $salesChannelContext;
        $this->orderEntity = $orderEntity;
    }

    public function getPaymentMethod(): PaymentMethodEntity
    {
        return $this->paymentMethod;
    }

    public function getProfileConfig(): ProfileConfigEntity
    {
        return $this->profileConfig;
    }

    public function getMethodConfig(): ProfileConfigMethodEntity
    {
        return $this->methodConfig;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * if <code>$isAvailable</code> is false, the event will stopped.
     */
    public function setIsAvailable(bool $isAvailable): void
    {
        if ($isAvailable === false) {
            $this->stopPropagation();
        }
        $this->isAvailable = $isAvailable;
    }

    public function getOrderEntity(): ?OrderEntity
    {
        return $this->orderEntity;
    }
}
