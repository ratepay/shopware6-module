<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Event;

use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Shopware\Core\Checkout\Order\OrderEntity;

class OrderExtensionDataBuilt
{
    private OrderEntity $orderEntity;

    private PaymentRequestData $paymentRequestData;

    private array $data;

    public function __construct(OrderEntity $orderEntity, PaymentRequestData $paymentRequestData, array $data)
    {
        $this->orderEntity = $orderEntity;
        $this->paymentRequestData = $paymentRequestData;
        $this->data = $data;
    }

    public function getOrder(): OrderEntity
    {
        return $this->orderEntity;
    }

    public function getPaymentRequestData(): PaymentRequestData
    {
        return $this->paymentRequestData;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
