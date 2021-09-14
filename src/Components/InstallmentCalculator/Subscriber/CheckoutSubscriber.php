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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutSubscriber implements EventSubscriberInterface
{
    private InstallmentService $installmentService;

    public function __construct(InstallmentService $installmentService)
    {
        $this->installmentService = $installmentService;
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
            $installmentCalculator = $this->installmentService->getInstallmentCalculatorData($salesChannelContext);

            $calcContext = (new InstallmentCalculatorContext(
                $salesChannelContext,
                $installmentCalculator['defaults']['type'],
                $installmentCalculator['defaults']['value'])
            )->setOrder($order);

            $vars = $this->installmentService->getInstallmentPlanTwigVars($calcContext);
            $vars['calculator'] = $installmentCalculator;

            $extension->offsetSet('installment', $vars);
        }
    }

}
