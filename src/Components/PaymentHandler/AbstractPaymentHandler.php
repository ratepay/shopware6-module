<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler;

use DateTimeInterface;
use InvalidArgumentException;
use RatePAY\Model\Response\PaymentRequest;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\Birthday;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\BirthdayNotBlank;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\IsOfLegalAge;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\BeforePaymentEvent;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentFailedEvent;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentSuccessfulEvent;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\ValidationDefinitionCollectEvent;
use Ratepay\RpayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Ratepay\RpayPayments\Exception\RatepayException;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\SynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Exception\SyncPaymentProcessException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\NotBlank;

abstract class AbstractPaymentHandler implements SynchronousPaymentHandlerInterface
{
    /**
     * @var string
     */
    public const ERROR_SNIPPET_VIOLATION_PREFIX = 'VIOLATION::';

    private PaymentRequestService $paymentRequestService;

    private EntityRepository $orderRepository;

    private EventDispatcherInterface $eventDispatcher;

    private ConfigService $configService;

    private EntityRepository $profileRepository;

    private RequestStack $requestStack;

    public function __construct(
        EntityRepository $orderRepository,
        EntityRepository $profileRepository,
        PaymentRequestService $paymentRequestService,
        EventDispatcherInterface $eventDispatcher,
        ConfigService $configService,
        RequestStack $requestStack
    )
    {
        $this->orderRepository = $orderRepository;
        $this->paymentRequestService = $paymentRequestService;
        $this->eventDispatcher = $eventDispatcher;
        $this->configService = $configService;
        $this->profileRepository = $profileRepository;
        $this->requestStack = $requestStack;
    }

    public function pay(SyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): void
    {
        $dataBag = $dataBag->get('paymentDetails', $dataBag); // data from pwa
        /** @var DataBag $ratepayData */
        $ratepayData = $dataBag->get('ratepay', new DataBag([]));

        $order = $this->getOrderWithAssociations($transaction->getOrder(), $salesChannelContext->getContext());

        if (!$order instanceof OrderEntity || $ratepayData->count() === 0) {
            throw new SyncPaymentProcessException($transaction->getOrderTransaction()->getId(), 'unknown error during payment');
        }

        try {

            $paymentRequestData = new PaymentRequestData(
                $salesChannelContext,
                $order,
                $transaction->getOrderTransaction(),
                $dataBag,
                $ratepayData->get('transactionId'),
                $this->configService->isSendDiscountsAsCartItem(),
                $this->configService->isSendShippingCostsAsCartItem()
            );

            if ($ratepayData->has('profile_uuid')) {
                $profile = $this->profileRepository->search(new Criteria([$ratepayData->get('profile_uuid')]), Context::createDefaultContext())->first();
                if (!$profile) {
                    throw new ProfileNotFoundException();
                }

                $paymentRequestData->setProfileConfig($profile);
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
                // will be caught a few lines later.
                throw new RatepayException($response->getCustomerMessage() ?: $response->getReasonMessage());
            }
        } catch (RatepayException $ratepayException) {
            $this->eventDispatcher->dispatch(new PaymentFailedEvent(
                $order,
                $transaction,
                $dataBag,
                $salesChannelContext,
                $response ?? null,
                $ratepayException->getPrevious() ?? $ratepayException
            ));

            if (($session = $this->requestStack->getSession()) instanceof Session) {
                $session->getFlashBag()->add(StorefrontController::DANGER, $ratepayException->getMessage());
            }

            throw new SyncPaymentProcessException($transaction->getOrderTransaction()->getId(), $ratepayException->getMessage());
        }
    }

    protected function getOrderWithAssociations(OrderEntity $order, Context $context): ?OrderEntity
    {
        return $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($order->getId()), $context)->first();
    }

    /**
     * @param OrderEntity|SalesChannelContext $baseData
     */
    public function getValidationDefinitions(DataBag $requestDataBag, $baseData): array
    {
        $validations = [
            'transactionId' => [
                new NotBlank(['message' => 'Unknown error.']),
            ],
        ];

        /** @var DataBag $ratepayData */
        $ratepayData = $requestDataBag->get('ratepay');

        if ($baseData instanceof SalesChannelContext) {
            $birthday = $baseData->getCustomer()->getBirthday();
            $isCompany = !empty($baseData->getCustomer()->getActiveBillingAddress()->getCompany());
        } elseif ($baseData instanceof OrderEntity) {
            $birthday = $baseData->getOrderCustomer()->getCustomer()->getBirthday();
            $isCompany = !empty($baseData->getAddresses()->get($baseData->getBillingAddressId())->getCompany());
        } else {
            throw new InvalidArgumentException('please provide a ' . SalesChannelContext::class . ' or an ' . OrderEntity::class . '. You provided a ' . get_class($baseData) . ' object');
        }

        if ($ratepayData->get('birthday') || (!$birthday instanceof DateTimeInterface && $isCompany === false)) {
            $validations['birthday'] = [
                new BirthdayNotBlank(),
                new Birthday(['message' => self::ERROR_SNIPPET_VIOLATION_PREFIX . Birthday::ERROR_NAME]),
                new IsOfLegalAge(['message' => self::ERROR_SNIPPET_VIOLATION_PREFIX . IsOfLegalAge::TOO_YOUNG_ERROR_NAME]),
            ];
        }

        /** @var ValidationDefinitionCollectEvent $event */
        $event = $this->eventDispatcher->dispatch(new ValidationDefinitionCollectEvent($validations, $requestDataBag, $baseData));

        return $event->getDefinitions();
    }
}
