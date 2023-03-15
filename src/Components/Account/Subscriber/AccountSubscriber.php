<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Account\Subscriber;

use Ratepay\RpayPayments\Util\RequestHelper;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Ratepay\RpayPayments\Components\Account\Event\PaymentUpdateRequestBagValidatedEvent;
use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\PaymentFailedEvent;
use Ratepay\RpayPayments\Components\RedirectException\Exception\ForwardException;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Ratepay\RpayPayments\Util\DataValidationHelper;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerRegistry;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Framework\Validation\DataValidationDefinition;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Storefront\Event\RouteRequest\HandlePaymentMethodRouteRequestEvent;
use Shopware\Storefront\Event\RouteRequest\SetPaymentOrderRouteRequestEvent;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccountSubscriber implements EventSubscriberInterface
{
    protected ExtensionService $extensionService;

    private DataValidator $dataValidator;

    /**
     * the interface has been deprecated, but shopware is using the Interface in a decorator for the repository.
     * so it will crash, if we are only using EntityRepository, cause an object of the decorator got injected into the constructor.
     *
     * After Shopware has removed the decorator, we can replace this by a normal definition
     * @var EntityRepository|\Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface
     * TODO remove comment on Shopware Version 6.5.0.0 & readd type int & change constructor argument type
     */
    private $paymentMethodRepository;

    private PaymentHandlerRegistry $paymentHandlerRegistry;

    private EntityRepository $orderRepository;

    private EventDispatcherInterface $eventDispatcher;

    private OrderTransactionStateHandler $orderTransactionStateHandler;

    public function __construct(
        ExtensionService $extensionService,
        PaymentHandlerRegistry $paymentHandlerRegistry,
        $paymentMethodRepository,
        EntityRepository $orderRepository,
        DataValidator $dataValidator,
        EventDispatcherInterface $eventDispatcher,
        OrderTransactionStateHandler $orderTransactionStateHandler
    )
    {
        $this->extensionService = $extensionService;
        $this->dataValidator = $dataValidator;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->paymentHandlerRegistry = $paymentHandlerRegistry;
        $this->orderRepository = $orderRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->orderTransactionStateHandler = $orderTransactionStateHandler;
    }

    /**
     * @return array{AccountEditOrderPageLoadedEvent: string[]|int[], HandlePaymentMethodRouteRequestEvent: string, SetPaymentOrderRouteRequestEvent: string}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AccountEditOrderPageLoadedEvent::class => ['addRatepayTemplateData', 310],
            HandlePaymentMethodRouteRequestEvent::class => 'onHandlePaymentMethodRouteRequest',
            SetPaymentOrderRouteRequestEvent::class => 'onPaymentOrderRouteRequest',
        ];
    }

    public function addRatepayTemplateData(AccountEditOrderPageLoadedEvent $event): void
    {
        $page = $event->getPage();
        $order = $page->getOrder();
        /** @var RatepayOrderDataEntity $ratepayData */
        $ratepayData = $order->getExtension(OrderExtension::EXTENSION_NAME);
        if ($ratepayData && MethodHelper::isRatepayOrder($order) && $ratepayData->isSuccessful()) {
            // You can't change the payment if it is a ratepay order
            $page->setPaymentChangeable(false);
        } else {
            $order = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($order->getId()), $event->getContext())->first();
            // Payment change is allowed, prepare ratepay payment data if a ratepay payment method is selected
            $paymentMethod = $event->getSalesChannelContext()->getPaymentMethod();
            if (MethodHelper::isRatepayMethod($paymentMethod->getHandlerIdentifier()) &&
                $event->getPage()->getPaymentMethods()->has($paymentMethod->getId())
            ) {
                $extension = $this->extensionService->buildPaymentDataExtension(
                    $event->getSalesChannelContext(),
                    $order,
                    $event->getRequest()
                );
                if ($extension !== null) {
                    $event->getPage()->addExtension(ExtensionService::PAYMENT_PAGE_EXTENSION_NAME, $extension);
                }
            }
        }
    }

    public function onHandlePaymentMethodRouteRequest(HandlePaymentMethodRouteRequestEvent $event): void
    {
        if ($event->getStorefrontRequest()->request->has('ratepay')) {
            $event->getStoreApiRequest()->request->set(
                'ratepay',
                RequestHelper::getArrayBag($event->getStorefrontRequest(), 'ratepay')->all()
            );
        }
    }

    public function onPaymentOrderRouteRequest(SetPaymentOrderRouteRequestEvent $event): void
    {
        if ($event->getStorefrontRequest()->attributes->get('_route') !== 'frontend.account.edit-order.update-order') {
            // make sure that this subscriber only got processed when the order got updated (e.g. switch of payment method or failed payment)
            return;
        }

        $orderId = $event->getStoreApiRequest()->get('orderId');
        /** @var OrderEntity $orderEntity */
        $orderEntity = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderId), $event->getContext())->first();

        $paymentMethodId = $event->getStoreApiRequest()->get('paymentMethodId');
        /** @var PaymentMethodEntity $paymentMethod */
        $paymentMethod = $this->paymentMethodRepository->search(new Criteria([$paymentMethodId]), $event->getContext())->first();
        if ($orderEntity === null ||
            $paymentMethod === null ||
            !MethodHelper::isRatepayMethod($paymentMethod->getHandlerIdentifier())
        ) {
            // not a ratepay method - nothing to do.
            return;
        }

        /** @var AbstractPaymentHandler $paymentHandler */
        $paymentHandler = $this->paymentHandlerRegistry->getPaymentMethodHandler($paymentMethod->getId());

        // we need to add some functionality to validate the payment data and the response from the gateway.
        // cause shopware do have the opportunity to output custom messages, we will throw an ForwardException, which
        // got caught in the `RedirectException` component and will be rewritten into forward.

        // we must validate the ratepay data on our own. to prevent errors with other extensions, we will only validate ratepay-data.
        $requestData = new DataBag($event->getStorefrontRequest()->request->all());
        $ratepayData = $requestData->get('ratepay');

        $validationDefinitions = $paymentHandler->getValidationDefinitions($requestData, $orderEntity);
        $definition = new DataValidationDefinition();
        $definition->addSub('ratepay', DataValidationHelper::addSubConstraints(new DataValidationDefinition(), $validationDefinitions));
        try {
            $this->dataValidator->validate(['ratepay' => $ratepayData->all()], $definition);
        } catch (ConstraintViolationException $constraintViolationException) {
            throw new ForwardException('frontend.account.edit-order.page', ['orderId' => $orderEntity->getId()], ['formViolations' => $constraintViolationException], $constraintViolationException);
        }

        $this->eventDispatcher->dispatch(new PaymentUpdateRequestBagValidatedEvent(
            $orderEntity,
            $paymentHandler,
            new RequestDataBag(['ratepay' => $ratepayData]),
            $event->getSalesChannelContext()
        ));

        // we register an payment-failed-subscriber to throw a forward-exception during the payment-complete in the update-payment process.
        // the listener must have a very low priority to make sure that all other event-subscriber can process
        $orderTransactionStateHandler = $this->orderTransactionStateHandler;
        $this->eventDispatcher->addListener(PaymentFailedEvent::class, static function (PaymentFailedEvent $event) use ($orderTransactionStateHandler): void {
            // set the transaction to failed. Without this, the customer will not be able to try a repayment.
            // NOTE: this is not required during the validation or the PaymentQuery, cause in this state,
            // the transaction is not set as "open". Only after the PaymentHandler got called
            $orderTransactionStateHandler->fail($event->getTransaction()->getOrderTransaction()->getId(), $event->getContext());

            // determine the correct error message for the customer.
            $message = $event->getException()->getMessage();
            if (($response = $event->getResponse()) !== null) {
                $message = $response->getCustomerMessage();
                $message = $message ?: $response->getReasonMessage();
            }
            throw new ForwardException('frontend.account.edit-order.page', ['orderId' => $event->getOrder()->getId()], ['ratepay-errors' => [$message]], $event->getException());
        }, -9_999_999);
    }
}
