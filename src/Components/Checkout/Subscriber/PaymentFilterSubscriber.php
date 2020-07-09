<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Subscriber;

use Ratepay\RatepayPayments\Components\Checkout\Service\PaymentFilterService;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentFilterSubscriber implements EventSubscriberInterface
{

    /**
     * @var PaymentFilterService
     */
    protected $paymentFilterService;

    public function __construct(PaymentFilterService $paymentFilterService)
    {
        $this->paymentFilterService = $paymentFilterService;
    }

    public static function getSubscribedEvents(): array
    {
        // ToDo: It's not enough to do it for the confirm page of the storefront. We have to do it for every sales
        //       channel, but there is no central event for it right now
        return [
            CheckoutConfirmPageLoadedEvent::class => 'filterPayments',
        ];
    }

    public function filterPayments(CheckoutConfirmPageLoadedEvent $event): void
    {
        $paymentMethods = $event->getPage()->getPaymentMethods();
        $filteredMethods = $this->paymentFilterService->filterPayments($paymentMethods, $event->getSalesChannelContext());
        $event->getPage()->setPaymentMethods($filteredMethods);
    }
}
