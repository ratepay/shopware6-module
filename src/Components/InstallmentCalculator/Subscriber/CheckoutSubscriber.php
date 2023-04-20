<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Subscriber;

use Ratepay\RpayPayments\Components\Checkout\Event\PaymentDataExtensionBuilt;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Model\InstallmentCalculatorContext;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Service\InstallmentService;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Order\OrderEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutSubscriber implements EventSubscriberInterface
{
    private InstallmentService $installmentService;

    private CartService $cartService;

    public function __construct(
        InstallmentService $installmentService,
        CartService $cartService
    )
    {
        $this->installmentService = $installmentService;
        $this->cartService = $cartService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PaymentDataExtensionBuilt::class => 'buildCheckoutExtension',
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

            if (!$order instanceof OrderEntity) {
                $calcContext->setTotalAmount($this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext)->getPrice()->getTotalPrice());
            }

            $installmentCalculator = $this->installmentService->getInstallmentCalculatorData($calcContext);

            $calcContext->setCalculationType($installmentCalculator['defaults']['type']);
            $calcContext->setCalculationValue($installmentCalculator['defaults']['value']);

            $vars = $this->installmentService->getInstallmentPlanTwigVars($calcContext);
            $vars['calculator'] = $installmentCalculator;

            $extension->offsetSet('installment', $vars);
        }
    }

}
