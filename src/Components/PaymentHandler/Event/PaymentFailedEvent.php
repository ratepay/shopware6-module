<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler\Event;

use Exception;
use RatePAY\Model\Response\PaymentRequest;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentFailedEvent extends AbstractPaymentEvent
{
    private ?Exception $exception;

    public function __construct(
        OrderEntity $order,
        SyncPaymentTransactionStruct $transaction,
        RequestDataBag $requestDataBag,
        SalesChannelContext $salesChannelContext,
        PaymentRequest $response = null,
        Exception $exception = null
    ) {
        parent::__construct($order, $transaction, $requestDataBag, $salesChannelContext, $response);
        $this->exception = $exception;
    }

    public function getException(): ?Exception
    {
        return $this->exception;
    }
}
