<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Core\Event;

use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class OrderItemOperationDoneEvent
{
    public function __construct(
        private readonly OrderEntity $orderEntity,
        private readonly OrderOperationData $orderOperationData,
        private readonly Context $context
    ) {
    }

    public function getOrderEntity(): OrderEntity
    {
        return $this->orderEntity;
    }

    public function getOrderOperationData(): OrderOperationData
    {
        return $this->orderOperationData;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
