<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Subscriber;

use RatePAY\Model\Response\PaymentQuery;
use Ratepay\RpayPayments\Components\Account\Event\PaymentUpdateRequestBagValidatedEvent;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentQueryData;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Service\Request\PaymentQueryService;
use Ratepay\RpayPayments\Components\PaymentHandler\AbstractPaymentHandler;
use Ratepay\RpayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RpayPayments\Components\RedirectException\Exception\ForwardException;
use Ratepay\RpayPayments\Exception\RatepayException;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Shopware\Core\Checkout\Order\OrderEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountSubscriber implements EventSubscriberInterface
{
    private PaymentQueryService $paymentQueryService;

    private OrderConverter $orderConverter;

    private TranslatorInterface $translator;

    private ConfigService $configService;

    public function __construct(
        TranslatorInterface $translator,
        PaymentQueryService $paymentQueryService,
        OrderConverter $orderConverter,
        ConfigService $configService
    )
    {
        $this->translator = $translator;
        $this->paymentQueryService = $paymentQueryService;
        $this->orderConverter = $orderConverter;
        $this->configService = $configService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentUpdateRequestBagValidatedEvent::class => 'onRequestDataValidated',
        ];
    }

    /**
     * @throws ForwardException
     */
    public function onRequestDataValidated(PaymentUpdateRequestBagValidatedEvent $event): void
    {
        try {
            $paymentQuery = $this->paymentQueryService->doRequest(new PaymentQueryData(
                $event->getSalesChannelContext(),
                $this->orderConverter->convertToCart($event->getOrderEntity(), $event->getContext()),
                $event->getRequestDataBag(),
                $event->getRequestDataBag()->get('ratepay')->get('transactionId'),
                $this->configService->isSendDiscountsAsCartItem(),
                $this->configService->isSendShippingCostsAsCartItem()
            ));

            $response = isset($paymentQuery) ? $paymentQuery->getResponse() : null;
            if ($response instanceof PaymentQuery && $response->getStatusCode() === 'OK') {
                if (!in_array($event->getPaymentHandler()::RATEPAY_METHOD, $response->getAdmittedPaymentMethods(), true)) {
                    $this->throwException(
                        $event->getOrderEntity(),
                        $this->translator->trans('error.' . AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . CheckoutValidationSubscriber::CODE_METHOD_NOT_AVAILABLE)
                    );
                }
            } else {
                $this->throwException(
                    $event->getOrderEntity(),
                    $response->getReasonMessage()
                );
            }
        } catch (RatepayException $ratepayException) {
            $this->throwException(
                $event->getOrderEntity(),
                $ratepayException->getMessage()
            );
        }
    }

    /**
     * @return never
     */
    private function throwException(OrderEntity $orderEntity, $message): void
    {
        throw new ForwardException('frontend.account.edit-order.page', ['orderId' => $orderEntity->getId()], ['ratepay-errors' => [$message]]);
    }
}
