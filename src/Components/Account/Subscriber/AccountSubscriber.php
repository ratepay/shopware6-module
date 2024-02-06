<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Account\Subscriber;

use Ratepay\RpayPayments\Components\Account\Event\PaymentUpdateRequestBagValidatedEvent;
use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\Checkout\Service\ExtensionService;
use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Ratepay\RpayPayments\Components\RedirectException\Exception\ForwardException;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Ratepay\RpayPayments\Util\DataValidationHelper;
use Ratepay\RpayPayments\Util\MethodHelper;
use Ratepay\RpayPayments\Util\RequestHelper;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerRegistry;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayStruct;
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
    public function __construct(
        private readonly ExtensionService $extensionService,
        private readonly PaymentHandlerRegistry $paymentHandlerRegistry,
        private readonly EntityRepository $paymentMethodRepository,
        private readonly EntityRepository $orderRepository,
        private readonly DataValidator $dataValidator,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

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
        /** @var RatepayOrderDataEntity|null $ratepayData */
        $ratepayData = $order->getExtension(OrderExtension::EXTENSION_NAME);
        if ($ratepayData && MethodHelper::isRatepayOrder($order) && $ratepayData->isSuccessful()) {
            // You can't change the payment if it is a ratepay order
            $page->setPaymentChangeable(false);
        } else {
            /** @var OrderEntity $order */
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
                if ($extension instanceof ArrayStruct) {
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
        $orderEntity = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($orderId), $event->getContext())->first();

        $paymentMethodId = $event->getStoreApiRequest()->get('paymentMethodId');
        $paymentMethod = $this->paymentMethodRepository->search(new Criteria([$paymentMethodId]), $event->getContext())->first();
        if (!$orderEntity instanceof OrderEntity ||
            !$paymentMethod instanceof PaymentMethodEntity ||
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
            $this->dataValidator->validate([
                'ratepay' => $ratepayData->all(),
            ], $definition);
        } catch (ConstraintViolationException $constraintViolationException) {
            throw new ForwardException('frontend.account.edit-order.page', [
                'orderId' => $orderEntity->getId(),
            ], [
                'formViolations' => $constraintViolationException,
            ], $constraintViolationException);
        }

        $this->eventDispatcher->dispatch(new PaymentUpdateRequestBagValidatedEvent(
            $orderEntity,
            $paymentHandler,
            new RequestDataBag([
                'ratepay' => $ratepayData,
            ]),
            $event->getSalesChannelContext()
        ));
    }
}
