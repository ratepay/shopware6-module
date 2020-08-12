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
use Ratepay\RatepayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RatepayPayments\Components\ProfileConfig\Service\ProfileConfigService;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Ratepay\RatepayPayments\Util\CriteriaHelper;
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
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var ProfileConfigService
     */
    private $profileConfigService;

    public function __construct(
        EntityRepositoryInterface $orderRepository,
        PaymentRequestService $paymentRequestService,
        ProfileConfigService $profileConfigService,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->orderRepository = $orderRepository;
        $this->paymentRequestService = $paymentRequestService;
        $this->eventDispatcher = $eventDispatcher;
        $this->profileConfigService = $profileConfigService;
    }

    public function pay(SyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): void
    {
        $order = $this->getOrderWithAssociations($transaction->getOrder(), $salesChannelContext->getContext());
        try {
            $profileConfig = $this->profileConfigService->getProfileConfigDefaultParams(
                $transaction->getOrderTransaction()->getPaymentMethod()->getId(),
                $order->getAddresses()->get($order->getBillingAddressId())->getCountry()->getIso(),
                $order->getDeliveries()->getShippingAddress()->first()->getCountry()->getIso(),
                $order->getSalesChannelId(),
                $order->getCurrency()->getIsoCode(),
                $salesChannelContext->getContext()
            );

            if ($profileConfig === null) {
                throw new ProfileNotFoundException();
            }

            $response = $this->paymentRequestService->doRequest(
                $salesChannelContext->getContext(),
                new PaymentRequestData(
                    $salesChannelContext,
                    $order,
                    $transaction->getOrderTransaction(),
                    $profileConfig,
                    $dataBag
                )
            );


            if ($response->getResponse()->isSuccessful()) {
                $this->eventDispatcher->dispatch(new PaymentSuccessfulEvent(
                    $order,
                    $transaction,
                    $dataBag,
                    $salesChannelContext,
                    $response->getResponse()
                ));

            } else {
                // will be catched a few lines later.
                throw new Exception($response->getResponse()->getReasonMessage());
            }

        } catch (Exception $e) {
            $this->eventDispatcher->dispatch(new PaymentFailedEvent(
                $order,
                $transaction,
                $dataBag,
                $salesChannelContext,
                isset($response) ? $response->getResponse() : null,
                $e
            ));
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

    public function getValidationDefinitions(SalesChannelContext $salesChannelContext): array
    {
        $validations = [];

        if (empty($salesChannelContext->getCustomer()->getActiveBillingAddress()->getCompany())) {
            $validations['birthday'] = [
                new NotBlank(['message' => 'ratepay.storefront.checkout.errors.missingBirthday']),
                new BirthdayConstraint('-18 years'),
            ];
        }
        return $validations;
    }

}
