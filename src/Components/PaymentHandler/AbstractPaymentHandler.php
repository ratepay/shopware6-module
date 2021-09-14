<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler;

use InvalidArgumentException;
use RatePAY\Model\Response\PaymentRequest;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\Birthday;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\BirthdayNotBlank;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\IsOfLegalAge;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\BeforePaymentEvent;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentFailedEvent;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentSuccessfulEvent;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\ProfileConfigService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Ratepay\RpayPayments\Components\RedirectException\Exception\ForwardException;
use Ratepay\RpayPayments\Exception\RatepayException;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\SynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Exception\SyncPaymentProcessException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\NotBlank;

abstract class AbstractPaymentHandler implements SynchronousPaymentHandlerInterface
{
    public const ERROR_SNIPPET_VIOLATION_PREFIX = 'VIOLATION::';

    private PaymentRequestService $paymentRequestService;

    private EntityRepositoryInterface $orderRepository;

    private EventDispatcherInterface $eventDispatcher;

    private ProfileConfigService $profileConfigService;

    public function __construct(
        EntityRepositoryInterface $orderRepository,
        PaymentRequestService $paymentRequestService,
        ProfileConfigService $profileConfigService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentRequestService = $paymentRequestService;
        $this->eventDispatcher = $eventDispatcher;
        $this->profileConfigService = $profileConfigService;
    }

    public function pay(SyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): void
    {
        /** @var ParameterBag $ratepayData */
        $ratepayData = $dataBag->get('ratepay', new ParameterBag([]));

        $order = $this->getOrderWithAssociations($transaction->getOrder(), $salesChannelContext->getContext());

        if ($order === null || $ratepayData->count() === 0) {
            throw new SyncPaymentProcessException($transaction->getOrderTransaction()->getId(), 'unknown error during payment');
        }
        try {

            $paymentRequestData = new PaymentRequestData(
                $salesChannelContext,
                $order,
                $transaction->getOrderTransaction(),
                $dataBag,
                $ratepayData->get('transactionId')
            );

            if($ratepayData->has('profile_uuid')) {
                $paymentRequestData->setProfileConfig(
                    $this->profileConfigService->getProfileConfigById($ratepayData->get('profile_uuid'), $salesChannelContext->getContext())
                );
            }

            $this->eventDispatcher->dispatch(new BeforePaymentEvent($paymentRequestData));

            /** @var PaymentRequest $response */
            $requestBuilder = $this->paymentRequestService->doRequest($paymentRequestData);
            $response = $requestBuilder->getResponse();

            if ($response->isSuccessful()) {
                $this->eventDispatcher->dispatch(new PaymentSuccessfulEvent(
                    $order,
                    $transaction,
                    $dataBag,
                    $salesChannelContext,
                    $response
                ));
            } else {
                // will be catched a few lines later.
                throw new RatepayException($response->getCustomerMessage() ?: $response->getReasonMessage());
            }
        } catch (RatepayException $e) {
            $this->eventDispatcher->dispatch(new PaymentFailedEvent(
                $order,
                $transaction,
                $dataBag,
                $salesChannelContext,
                isset($response) ? $response : null,
                $e->getPrevious() ?? $e
            ));

            throw new ForwardException('frontend.account.edit-order.page', ['orderId' => $order->getId()], ['ratepay-errors' => [$e->getMessage()]], new SyncPaymentProcessException($transaction->getOrderTransaction()->getId(), $e->getMessage()));
        }
    }

    protected function getOrderWithAssociations(OrderEntity $order, Context $context): ?OrderEntity
    {
        return $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($order->getId()), $context)->first();
    }

    /**
     * @param OrderEntity|SalesChannelContext $baseData
     */
    public function getValidationDefinitions(Request $request, $baseData): array
    {
        $validations = [
            'transactionId' => [
                new NotBlank(['message' => 'Unknown error.']),
            ],
        ];

        $ratepayData = $request->get('ratepay');

        if ($baseData instanceof SalesChannelContext) {
            $birthday = $baseData->getCustomer()->getBirthday();
            $isCompany = !empty($baseData->getCustomer()->getActiveBillingAddress()->getCompany());
        } elseif ($baseData instanceof OrderEntity) {
            $birthday = $baseData->getOrderCustomer()->getCustomer()->getBirthday();
            $isCompany = !empty($baseData->getAddresses()->get($baseData->getBillingAddressId())->getCompany());
        } else {
            throw new InvalidArgumentException('please provide a ' . SalesChannelContext::class . ' or an ' . OrderEntity::class . '. You provided a ' . get_class($baseData) . ' object');
        }

        if (isset($ratepayData['birthday']) || ($birthday === null && $isCompany === false)) {
            $validations['birthday'] = [
                new BirthdayNotBlank(),
                new Birthday(['message' => self::ERROR_SNIPPET_VIOLATION_PREFIX . Birthday::ERROR_NAME]),
                new IsOfLegalAge(['message' => self::ERROR_SNIPPET_VIOLATION_PREFIX . IsOfLegalAge::TOO_YOUNG_ERROR_NAME]),
            ];
        }

        return $validations;
    }
}
