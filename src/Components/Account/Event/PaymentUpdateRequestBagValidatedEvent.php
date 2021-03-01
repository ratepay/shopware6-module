<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Account\Event;

use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareEvent;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class PaymentUpdateRequestBagValidatedEvent extends Event implements ShopwareEvent
{
    /**
     * @var OrderEntity
     */
    private $orderEntity;

    /**
     * @var RequestDataBag
     */
    private $requestDataBag;

    /**
     * @var SalesChannelContext
     */
    private $salesChannelContext;

    /**
     * @var AbstractPaymentHandler
     */
    private $paymentHandler;

    public function __construct(
        OrderEntity $orderEntity,
        AbstractPaymentHandler $paymentHandler,
        RequestDataBag $requestDataBag,
        SalesChannelContext $salesChannelContext
    ) {
        $this->orderEntity = $orderEntity;
        $this->paymentHandler = $paymentHandler;
        $this->requestDataBag = $requestDataBag;
        $this->salesChannelContext = $salesChannelContext;
    }

    public function getOrderEntity(): OrderEntity
    {
        return $this->orderEntity;
    }

    public function getRequestDataBag(): RequestDataBag
    {
        return $this->requestDataBag;
    }

    public function getPaymentHandler(): AbstractPaymentHandler
    {
        return $this->paymentHandler;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function getContext(): Context
    {
        return $this->salesChannelContext->getContext();
    }
}
