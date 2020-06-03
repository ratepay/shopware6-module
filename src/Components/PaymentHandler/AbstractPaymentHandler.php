<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\PaymentHandler;


use Exception;
use Ratepay\RatepayPayments\Components\PaymentHandler\Constraint\BirthdayConstraint;
use Ratepay\RatepayPayments\Components\PaymentHandler\Event\PaymentFailedEvent;
use Ratepay\RatepayPayments\Components\PaymentHandler\Event\PaymentSuccessfulEvent;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\Request\PaymentRequestService;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\SynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Exception\SyncPaymentProcessException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

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
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        OrderTransactionStateHandler $transactionStateHandler,
        EntityRepositoryInterface $orderRepository,
        PaymentRequestService $paymentRequestService,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->transactionStateHandler = $transactionStateHandler;
        $this->orderRepository = $orderRepository;
        $this->paymentRequestService = $paymentRequestService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function pay(SyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): void
    {
        $order = $this->getOrderWithAssociations($transaction->getOrder(), $salesChannelContext->getContext());
        try {
            $this->paymentRequestService->setTransaction($order, $transaction);
            $this->paymentRequestService->setRequestDataBag($dataBag);
            $response = $this->paymentRequestService->doRequest();
            if ($response->getResponse()->isSuccessful()) {
                $this->eventDispatcher->dispatch(new PaymentSuccessfulEvent($transaction, $dataBag, $salesChannelContext, $response->getResponse()));
            } else {
                throw new Exception($response->getResponse()->getReasonMessage());
            }
        } catch (Exception $e) {
            $this->eventDispatcher->dispatch(new PaymentFailedEvent($transaction, $dataBag, $salesChannelContext, isset($response) ? $response->getResponse() : null));
            throw new SyncPaymentProcessException($transaction->getOrderTransaction()->getId(), $e->getMessage());
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

    public function getValidationDefinitions(SalesChannelContext $salesChannelContext)
    {
        $validations = [];
        
        if (empty($salesChannelContext->getCustomer()->getActiveBillingAddress()->getCompany())) {
            $validations['phone'] = [new NotBlank()];
            $validations['vatid'] = [new NotBlank()];
        } else {
            $validations['birthday'] = [new NotBlank(), new BirthdayConstraint('-18 years')];
        }
        return $validations;
    }

}
