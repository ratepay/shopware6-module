<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\PaymentHandler;

use DateTimeInterface;
use RatePAY\Model\Response\PaymentRequest;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\Birthday;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\BirthdayNotBlank;
use Ratepay\RpayPayments\Components\PaymentHandler\Constraint\IsOfLegalAge;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\BeforePaymentEvent;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentFailedEvent;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentSuccessfulEvent;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\ValidationDefinitionCollectEvent;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileByOrderEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileSearchService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Ratepay\RpayPayments\Core\PluginConfigService;
use Ratepay\RpayPayments\Exception\RatepayException;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Ratepay\RpayPayments\Util\RequestHelper;
use RuntimeException;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerType;
use Shopware\Core\Checkout\Payment\Cart\PaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\PaymentException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Throwable;

abstract class AbstractPaymentHandler extends \Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AbstractPaymentHandler
{
    /**
     * @var string
     */
    final public const ERROR_SNIPPET_VIOLATION_PREFIX = 'VIOLATION::';

    public function __construct(
        private readonly EntityRepository $orderRepository,
        private readonly EntityRepository $orderTransactionRepository,
        private readonly PaymentRequestService $paymentRequestService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly PluginConfigService $configService,
        private readonly RequestStack $requestStack,
        private readonly ProfileSearchService $profileSearchService,
        private readonly ProfileByOrderEntity $profileByOrderEntitySearchService
    ) {
    }

    abstract public static function getRatepayPaymentMethodName(): string;

    public function supports(PaymentHandlerType $type, string $paymentMethodId, Context $context): bool
    {
        // This payment handler does not support recurring payments nor refunds
        return false;
    }

    public function pay(Request $request, PaymentTransactionStruct $transaction, Context $context, ?Struct $validateStruct): ?RedirectResponse
    {
        $salesChannelContext = $request->attributes->get('sw-sales-channel-context');

        $data = $request->get('ratepay');
        $ratepayDataBag = new RequestDataBag($data);

        $dataBag = new RequestDataBag();
        $dataBag->set('paymentDetails', [
            'ratepay' => $ratepayDataBag,
        ]);

        $ratepayData = RequestHelper::getRatepayData($dataBag) ?: new ParameterBag();

        $orderTransactionId = $transaction->getOrderTransactionId();
        $orderTransaction = $this->getOrderTransactionById($orderTransactionId, $context);

        $orderEntity = $orderTransaction?->getOrder();
        $order = $this->getOrderWithAssociations($orderEntity, Context::createDefaultContext());

        $paymentMethod = $order->getTransactions()->last()->getPaymentMethod();
        $orderTransaction->setPaymentMethod($paymentMethod);

        if (!$order instanceof OrderEntity || count($ratepayData) === 0 || !$orderTransaction) {
            throw $this->syncProcessInterrupted($orderTransactionId, 'unknown error during payment');
        }

        try {
            $paymentRequestData = new PaymentRequestData(
                $salesChannelContext,
                $order,
                $orderTransaction,
                $dataBag,
                $this->configService->isSendDiscountsAsCartItem(),
                $this->configService->isSendShippingCostsAsCartItem()
            );

            if ($ratepayData->has('profile_uuid')) {
                $profile = $this->profileSearchService->getProfileConfigById($ratepayData->get('profile_uuid'));
            } else {
                $profile = $this->profileByOrderEntitySearchService->search(
                    $this->profileByOrderEntitySearchService->createSearchObject($order),
                    $salesChannelContext
                )->first();
            }

            if (!$profile instanceof ProfileConfigEntity) {
                throw new ProfileNotFoundException();
            }

            $paymentRequestData->setProfileConfig($profile);

            $this->eventDispatcher->dispatch(new BeforePaymentEvent($paymentRequestData));

            $requestBuilder = $this->paymentRequestService->doRequest($paymentRequestData);
            /** @var PaymentRequest $response */
            $response = $requestBuilder->getResponse();

            if ($response->isSuccessful()) {
                $this->eventDispatcher->dispatch(new PaymentSuccessfulEvent(
                    $order,
                    $orderTransaction,
                    $dataBag,
                    $salesChannelContext,
                    $response
                ));
            } else {
                $message = null;
                if (method_exists($response, 'getCustomerMessage')) {
                    $message = $response->getCustomerMessage();
                }

                if (empty($message)) {
                    $message = (string) $response->getReasonMessage();
                }

                // will be caught a few lines later.
                throw new RatepayException($message);
            }
        } catch (RatepayException $ratepayException) {
            $this->eventDispatcher->dispatch(new PaymentFailedEvent(
                $order,
                $orderTransaction,
                $dataBag,
                $salesChannelContext,
                $response ?? null,
                $ratepayException->getPrevious() ?? $ratepayException
            ));

            if (($session = $this->requestStack->getSession()) instanceof Session) {
                $session->getFlashBag()->add(StorefrontController::DANGER, $ratepayException->getMessage());
            }

            throw $this->syncProcessInterrupted($orderTransactionId, $ratepayException->getMessage(), $ratepayException);
        }

        return null;
    }

    public function getValidationDefinitions(DataBag $requestDataBag, SalesChannelContext $salesChannelContext, ?OrderEntity $orderEntity = null): array
    {
        $validations = [];

        /** @var DataBag $ratepayData */
        $ratepayData = RequestHelper::getRatepayData($requestDataBag) ?: new ParameterBag();

        if ($orderEntity instanceof OrderEntity) {
            $birthday = $orderEntity->getOrderCustomer()->getCustomer()->getBirthday();
            $isCompany = !empty($orderEntity->getAddresses()->get($orderEntity->getBillingAddressId())->getCompany());
        } else {
            $birthday = $salesChannelContext->getCustomer()->getBirthday();
            $isCompany = !empty($salesChannelContext->getCustomer()->getActiveBillingAddress()->getCompany());
        }

        if ($ratepayData->get('birthday') || (!$birthday instanceof DateTimeInterface && $isCompany === false)) {
            $validations['birthday'] = [
                new BirthdayNotBlank(),
                new Birthday([
                    'message' => self::ERROR_SNIPPET_VIOLATION_PREFIX . Birthday::ERROR_NAME,
                ]),
                new IsOfLegalAge([
                    'message' => self::ERROR_SNIPPET_VIOLATION_PREFIX . IsOfLegalAge::TOO_YOUNG_ERROR_NAME,
                ]),
            ];
        }

        /** @var ValidationDefinitionCollectEvent $event */
        $event = $this->eventDispatcher->dispatch(new ValidationDefinitionCollectEvent($validations, $requestDataBag, $salesChannelContext, $orderEntity));

        return $event->getDefinitions();
    }

    protected function getOrderWithAssociations(OrderEntity $order, Context $context): ?OrderEntity
    {
        return $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($order->getId()), $context)->first();
    }

    private function syncProcessInterrupted(string $orderTransactionId, string $errorMessage, ?Throwable $e = null): Throwable
    {
        if (class_exists(PaymentException::class)) {
            return PaymentException::syncProcessInterrupted($orderTransactionId, $errorMessage, $e);
        } elseif (class_exists(SyncPaymentProcessException::class)) {
            // required for shopware version <= 6.5.3
            return new SyncPaymentProcessException($orderTransactionId, $errorMessage, $e); // @phpstan-ignore-line
        }

        // should never occur - just to be safe
        return new RuntimeException('payment interrupted: ' . $errorMessage, 0, $e);
    }

    private function getOrderTransactionById(string $orderTransactionId, Context $context): ?OrderTransactionEntity
    {
        $criteria = new Criteria([$orderTransactionId]);
        $criteria->addAssociation('order');

        return $this->orderTransactionRepository->search($criteria, $context::createDefaultContext())->first();
    }
}
