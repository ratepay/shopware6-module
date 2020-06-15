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
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\Request\PaymentRequestService;
use Ratepay\RatepayPayments\Util\CriteriaHelper;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\SynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Exception\SyncPaymentProcessException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
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
            $response = $this->paymentRequestService->doRequest(
                $salesChannelContext->getContext(),
                new PaymentRequestData(
                    $order,
                    $transaction->getOrderTransaction(),
                    $dataBag
                )
            );
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
        return $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($order->getId()), $context)->first();
    }

    public function getValidationDefinitions(SalesChannelContext $salesChannelContext)
    {
        $validations = [];

        if (!empty($salesChannelContext->getCustomer()->getActiveBillingAddress()->getCompany())) {
            // phone is not required anymore
            //$validations['phone'] = [new NotBlank(['message' => 'ratepay.storefront.checkout.errors.missingPhone'])];
            $validations['vatId'] = [new NotBlank(['message' => 'ratepay.storefront.checkout.errors.missingVatId'])];
        } else {
            $validations['birthday'] = [new NotBlank(['message' => 'ratepay.storefront.checkout.errors.missingBirthday']), new BirthdayConstraint('-18 years')];
        }
        return $validations;
    }

}
