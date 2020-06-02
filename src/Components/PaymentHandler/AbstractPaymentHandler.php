<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler;


use Ratepay\RatepayPayments\Components\RatepayApi\Services\Request\PaymentRequestService;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\SynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

abstract class AbstractPaymentHandler implements SynchronousPaymentHandlerInterface
{

    /**
     * @var PaymentRequestService
     */
    private $paymentRequestService;
    /**
     * @var OrderTransactionStateHandler
     */
    private $transactionStateHandler;
    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        OrderTransactionStateHandler $transactionStateHandler,
        EntityRepositoryInterface $orderRepository,
        PaymentRequestService $paymentRequestService)
    {
        $this->transactionStateHandler = $transactionStateHandler;
        $this->orderRepository = $orderRepository;
        $this->paymentRequestService = $paymentRequestService;
    }

    public function pay(SyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): void
    {
        $order = $this->getOrderWithAssociations($transaction->getOrder(), $salesChannelContext->getContext());
        $this->paymentRequestService->setTransaction($order, $transaction);
        try {
            $this->paymentRequestService->doRequest();
        } catch (\Exception $e) {
            throw $e;
            //throw new SyncPaymentProcessException($transaction->getOrderTransaction()->getId(), 'hihi bestellung abgebrochen');
        }
    }

    /**
     * @param OrderEntity $order
     * @param Context $context
     * @return OrderEntity|null
     */
    protected function getOrderWithAssociations(OrderEntity $order, Context $context)
    {
        $criteria = new Criteria([$order->getId()]);
        $criteria->addAssociation('currency');
        $criteria->addAssociation('language.locale');
        $criteria->addAssociation('addresses');
        $criteria->addAssociation('addresses.country');
        $criteria->addAssociation('addresses.salutation');
        $criteria->addAssociation('orderCustomer');
        $criteria->addAssociation('orderCustomer.customer');
        $criteria->addAssociation('lineItems');
        $criteria->addAssociation('lineItems.cover');
        $criteria->addAssociation('deliveries');
        $criteria->addAssociation('deliveries.shippingMethod');
        $criteria->addAssociation('deliveries.positions');
        $criteria->addAssociation('deliveries.positions.orderLineItem');
        $criteria->addAssociation('deliveries.shippingOrderAddress');
        $criteria->addAssociation('deliveries.shippingOrderAddress.country');
        $criteria->addAssociation('deliveries.shippingOrderAddress.countryState');
        $criteria->addAssociation('deliveries.shippingOrderAddress.salutation');
        $criteria->addSorting(new FieldSorting('lineItems.createdAt'));

        return $this->orderRepository->search($criteria, $context)->first();
    }

}
