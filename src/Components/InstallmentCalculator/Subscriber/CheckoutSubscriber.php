<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Subscriber;

use Psr\Log\LoggerInterface;
use Ratepay\RpayPayments\Components\Checkout\Event\PaymentDataExtensionBuilt;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Model\InstallmentCalculatorContext;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Service\InstallmentService;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Checkout\Cart\Error\GenericCartError;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Adapter\Translation\AbstractTranslator;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Account\Order\AccountEditOrderPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Throwable;

class CheckoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly InstallmentService $installmentService,
        private readonly CartService $cartService,
        private readonly LoggerInterface $logger,
        private readonly AbstractTranslator $translator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentDataExtensionBuilt::class => 'buildCheckoutExtension',
            AccountEditOrderPageLoadedEvent::class => ['onAccountEditOrderPageLoaded', 300],
        ];
    }

    public function buildCheckoutExtension(PaymentDataExtensionBuilt $event): void
    {
        $paymentMethod = $event->getSalesChannelContext()->getPaymentMethod();
        $salesChannelContext = $event->getSalesChannelContext();
        $order = $event->getOrderEntity();
        $extension = $event->getExtension();

        if (MethodHelper::isInstallmentMethod($paymentMethod->getHandlerIdentifier())) {
            $calcContext = (new InstallmentCalculatorContext($salesChannelContext, '', null))
                ->setPaymentMethodId($paymentMethod->getId())
                ->setOrder($order);
            $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

            if (!$order instanceof OrderEntity) {
                $calcContext->setTotalAmount($cart->getPrice()->getTotalPrice());
            }

            try {
                $installmentCalculator = $this->installmentService->getInstallmentCalculatorData($calcContext);

                $calcContext->setCalculationType($installmentCalculator['defaults']['type']);
                $calcContext->setCalculationValue($installmentCalculator['defaults']['value']);

                $vars = $this->installmentService->getInstallmentPlanTwigVars($calcContext);
                $vars['calculator'] = $installmentCalculator;
                $extension->offsetSet('installment', $vars);
            } catch (Throwable $exception) {
                $this->logger->error('Ratepay installment can not be loaded. ' . $exception->getMessage(), [
                    'order_id' => $order?->getId(),
                    'payment_method_id' => $calcContext->getPaymentMethodId(),
                    'total_amount' => $calcContext->getTotalAmount(),
                    'calculation_type' => $calcContext->getCalculationType(),
                    'calculation_value' => $calcContext->getCalculationValue(),
                ]);

                if (!$order instanceof OrderEntity) {
                    $cart->addErrors(new GenericCartError(
                        'RATEPAY::INSTALLMENT_CAN_NOT_BE_LOADED',
                        'error.RATEPAY_INSTALLMENT_CAN_NOT_BE_LOADED',
                        [
                            'method' => $paymentMethod->getTranslated()['name'] ?? $paymentMethod->getName(),
                        ],
                        Error::LEVEL_ERROR,
                        true,
                        false,
                        true
                    ));
                }
            }
        }
    }

    public function onAccountEditOrderPageLoaded(AccountEditOrderPageLoadedEvent $event): void
    {
        $paymentMethod = $event->getSalesChannelContext()->getPaymentMethod();

        if (MethodHelper::isInstallmentMethod($paymentMethod->getHandlerIdentifier())) {
            /** @var ArrayStruct|null $ratepayData */
            $ratepayData = $event->getPage()->getExtension('ratepay');
            if ($ratepayData?->get('installment') === null) {
                $session = $event->getRequest()->getSession();
                if ($session instanceof FlashBagAwareSessionInterface) {
                    $session->getFlashbag()->add('danger', $this->translator->trans('checkout.error.RATEPAY_INSTALLMENT_CAN_NOT_BE_LOADED', [
                        '%method%' => $paymentMethod->getTranslated()['name'] ?? $paymentMethod->getName(),
                    ]));
                }
            }
        }
    }
}
