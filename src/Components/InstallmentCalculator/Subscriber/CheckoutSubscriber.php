<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\InstallmentCalculator\Subscriber;

use Ratepay\RatepayPayments\Components\InstallmentCalculator\Service\InstallmentService;
use Ratepay\RatepayPayments\Components\InstallmentCalculator\Util\MethodHelper;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutSubscriber implements EventSubscriberInterface
{

    /**
     * @var InstallmentService
     */
    private $installmentService;

    public function __construct(InstallmentService $installmentService)
    {
        $this->installmentService = $installmentService;
    }

    public static function getSubscribedEvents()
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => ['addRatepayTemplateData', 500]
        ];
    }

    /**
     * @param CheckoutConfirmPageLoadedEvent $event
     * @codeCoverageIgnore
     */
    public function addRatepayTemplateData(CheckoutConfirmPageLoadedEvent $event): void
    {
        $paymentMethod = $event->getSalesChannelContext()->getPaymentMethod();
        if (MethodHelper::isInstallmentMethod($paymentMethod->getHandlerIdentifier())) {
            $extension = $event->getPage()->getExtension('ratepay') ?? new ArrayStruct();

            $installmentCalculator = $this->installmentService->getInstallmentCalculatorData($event->getSalesChannelContext());

            $installmentPlan = $this->installmentService->getInstallmentPlanData(
                $event->getSalesChannelContext(),
                $installmentCalculator['defaults']['type'],
                $installmentCalculator['defaults']['value']
            );

            $extension->set('installment', [
                'translations' => $this->installmentService->getTranslations($event->getSalesChannelContext()),
                'calculator' => $installmentCalculator,
                'plan' => $installmentPlan
            ]);
            $event->getPage()->addExtension('ratepay', $extension);
        }

    }

}
