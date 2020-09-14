<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler\Event;


use Exception;
use RatePAY\Model\Response\PaymentRequest;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PaymentFailedEvent extends AbstractPaymentEvent
{

    /**
     * @var Exception
     */
    private $exception;

    public function __construct(
        OrderEntity $order,
        SyncPaymentTransactionStruct $transaction,
        RequestDataBag $requestDataBag,
        SalesChannelContext $salesChannelContext,
        PaymentRequest $response = null,
        Exception $exception = null
    )
    {
        parent::__construct($order, $transaction, $requestDataBag, $salesChannelContext, $response);
        $this->exception = $exception;
    }

    /**
     * @return Exception
     */
    public function getException(): Exception
    {
        return $this->exception;
    }

}
