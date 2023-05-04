<?php

declare(strict_types=1);

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
    private bool $isAvailable = true;

    public function __construct(
        private readonly PaymentMethodEntity $paymentMethod,
        private readonly ProfileConfigEntity $profileConfig,
        private readonly ProfileConfigMethodEntity $methodConfig,
        private readonly ?SalesChannelContext $salesChannelContext,
        private readonly ?OrderEntity $orderEntity = null
    ) {
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
