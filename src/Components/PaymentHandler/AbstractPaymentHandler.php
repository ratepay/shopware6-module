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
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileSearchService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\PaymentRequestService;
use Ratepay\RpayPayments\Exception\RatepayException;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Ratepay\RpayPayments\Util\RequestHelper;
use RuntimeException;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\SynchronousPaymentHandlerInterface;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Exception\SyncPaymentProcessException;
use Shopware\Core\Checkout\Payment\PaymentException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Throwable;

abstract class AbstractPaymentHandler implements SynchronousPaymentHandlerInterface
{
    /**
     * @var string
     */
    final public const ERROR_SNIPPET_VIOLATION_PREFIX = 'VIOLATION::';

    public function __construct(
        private readonly EntityRepository $orderRepository,
        private readonly PaymentRequestService $paymentRequestService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ConfigService $configService,
        private readonly RequestStack $requestStack,
        private readonly ProfileSearchService $profileSearchService
    ) {
    }

    abstract public static function getRatepayPaymentMethodName(): string;

    public function pay(SyncPaymentTransactionStruct $transaction, RequestDataBag $dataBag, SalesChannelContext $salesChannelContext): void
    {
        $ratepayData = RequestHelper::getRatepayData($dataBag) ?: new ParameterBag();

        $order = $this->getOrderWithAssociations($transaction->getOrder(), $salesChannelContext->getContext());

        if (!$order instanceof OrderEntity || $ratepayData->count() === 0) {
            throw $this->syncProcessInterrupted($transaction->getOrderTransaction()->getId(), 'unknown error during payment');
        }

        try {
            $paymentRequestData = new PaymentRequestData(
                $salesChannelContext,
                $order,
                $transaction->getOrderTransaction(),
                $dataBag,
                $this->configService->isSendDiscountsAsCartItem(),
                $this->configService->isSendShippingCostsAsCartItem()
            );

            if ($ratepayData->has('profile_uuid')) {
                $profile = $this->profileSearchService->getProfileConfigById($ratepayData->get('profile_uuid'));
                if (!$profile instanceof Entity) {
                    throw new ProfileNotFoundException();
                }

                $paymentRequestData->setProfileConfig($profile);
            }

            $this->eventDispatcher->dispatch(new BeforePaymentEvent($paymentRequestData));

            $requestBuilder = $this->paymentRequestService->doRequest($paymentRequestData);
            /** @var PaymentRequest $response */
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
                $transaction,
                $dataBag,
                $salesChannelContext,
                $response ?? null,
                $ratepayException->getPrevious() ?? $ratepayException
            ));

            if (($session = $this->requestStack->getSession()) instanceof Session) {
                $session->getFlashBag()->add(StorefrontController::DANGER, $ratepayException->getMessage());
            }

            throw $this->syncProcessInterrupted($transaction->getOrderTransaction()->getId(), $ratepayException->getMessage(), $ratepayException);
        }
    }

    /**
     * @param OrderEntity|SalesChannelContext $baseData
     */
    public function getValidationDefinitions(DataBag $requestDataBag, $baseData): array
    {
        $validations = [];

        /** @var DataBag $ratepayData */
        $ratepayData = RequestHelper::getRatepayData($requestDataBag) ?: new ParameterBag();

        if ($baseData instanceof SalesChannelContext) {
            $birthday = $baseData->getCustomer()->getBirthday();
            $isCompany = !empty($baseData->getCustomer()->getActiveBillingAddress()->getCompany());
        } elseif ($baseData instanceof OrderEntity) {
            $birthday = $baseData->getOrderCustomer()->getCustomer()->getBirthday();
            $isCompany = !empty($baseData->getAddresses()->get($baseData->getBillingAddressId())->getCompany());
        } else {
            throw new InvalidArgumentException('please provide a ' . SalesChannelContext::class . ' or an ' . OrderEntity::class . '. You provided a ' . $baseData::class . ' object');
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
        $event = $this->eventDispatcher->dispatch(new ValidationDefinitionCollectEvent($validations, $requestDataBag, $baseData));

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
}
