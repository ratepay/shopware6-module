<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Event;


use Ratepay\RatepayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RatepayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class RatepayPaymentFilterEvent extends Event
{

    /**
     * @var PaymentMethodEntity
     */
    private $paymentMethod;
    /**
     * @var SalesChannelContext
     */
    private $salesChannelContext;

    /**
     * @var bool
     */
    private $isAvailable = true;

    /**
     * @var Event|null
     */
    private $parentEvent;
    /**
     * @var ProfileConfigEntity
     */
    private $profileConfig;
    /**
     * @var ProfileConfigMethodEntity
     */
    private $methodConfig;

    public function __construct(
        PaymentMethodEntity $paymentMethod,
        ProfileConfigEntity $profileConfig,
        ProfileConfigMethodEntity $methodConfig,
        SalesChannelContext $salesChannelContext,
        Event $parentEvent = null
    )
    {
        $this->paymentMethod = $paymentMethod;
        $this->profileConfig = $profileConfig;
        $this->methodConfig = $methodConfig;
        $this->salesChannelContext = $salesChannelContext;
        $this->parentEvent = $parentEvent;
    }

    /**
     * @return PaymentMethodEntity
     */
    public function getPaymentMethod(): PaymentMethodEntity
    {
        return $this->paymentMethod;
    }

    /**
     * @return ProfileConfigEntity
     */
    public function getProfileConfig(): ProfileConfigEntity
    {
        return $this->profileConfig;
    }

    /**
     * @return ProfileConfigMethodEntity
     */
    public function getMethodConfig(): ProfileConfigMethodEntity
    {
        return $this->methodConfig;
    }

    /**
     * @return SalesChannelContext
     */
    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * @param bool $isAvailable
     */
    public function setIsAvailable(bool $isAvailable): void
    {
        $this->isAvailable = $isAvailable;
    }

    /**
     * @return Event|null
     */
    public function getParentEvent(): ?Event
    {
        return $this->parentEvent;
    }


}
