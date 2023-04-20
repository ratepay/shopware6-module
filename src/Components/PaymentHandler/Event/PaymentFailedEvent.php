<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler\Event;

use RatePAY\Model\Response\PaymentRequest;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Throwable;

class PaymentFailedEvent extends AbstractPaymentEvent
{
    private ?Throwable $exception;

    public function __construct(
        OrderEntity $order,
        SyncPaymentTransactionStruct $transaction,
        RequestDataBag $requestDataBag,
        SalesChannelContext $salesChannelContext,
        PaymentRequest $response = null,
        Throwable $exception = null
    ) {
        parent::__construct($order, $transaction, $requestDataBag, $salesChannelContext, $response);
        $this->exception = $exception;
    }

    public function getException(): ?Throwable
    {
        return $this->exception;
    }
}
