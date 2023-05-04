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
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly PaymentQueryService $paymentQueryService,
        private readonly OrderConverter $orderConverter,
        private readonly ConfigService $configService
    ) {
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

            $response = $paymentQuery->getResponse();
            if ($response instanceof PaymentQuery && $response->getStatusCode() === 'OK') {
                if (!in_array($event->getPaymentHandler()::getRatepayPaymentMethodName(), $response->getAdmittedPaymentMethods(), true)) {
                    throw $this->createException(
                        $event->getOrderEntity(),
                        $this->translator->trans('error.' . AbstractPaymentHandler::ERROR_SNIPPET_VIOLATION_PREFIX . CheckoutValidationSubscriber::CODE_METHOD_NOT_AVAILABLE)
                    );
                }
            } else {
                throw $this->createException(
                    $event->getOrderEntity(),
                    (string) $response->getReasonMessage()
                );
            }
        } catch (RatepayException $ratepayException) {
            throw $this->createException(
                $event->getOrderEntity(),
                $ratepayException->getMessage()
            );
        }
    }

    private function createException(OrderEntity $orderEntity, ?string $message): ForwardException
    {
        return (new ForwardException(
            'frontend.account.edit-order.page',
            [
                'orderId' => $orderEntity->getId(),
            ],
            [
                'ratepay-errors' => [$message],
            ]
        )
        )->setCustomerMessage($message);
    }
}
