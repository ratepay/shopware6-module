<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Subscriber;

use Ratepay\RatepayPayments\Components\PaymentHandler\DebitPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => 'addRatepayTemplateData'
        ];
    }

    /**
     * @param CheckoutConfirmPageLoadedEvent $event
     * @codeCoverageIgnore
     */
    public function addRatepayTemplateData(CheckoutConfirmPageLoadedEvent $event): void
    {
        $paymentMethod = $event->getSalesChannelContext()->getPaymentMethod();
        if (strpos($paymentMethod->getHandlerIdentifier(),'RatepayPayments') !== false){
            /* Get customer data for checkout form */
            $customerBirthday = $event->getSalesChannelContext()->getCustomer()->getBirthday();
            $customerBillingAddress = $event->getSalesChannelContext()->getCustomer()->getActiveBillingAddress();
            $customerVatId = $customerBillingAddress->getVatId();
            $customerPhoneNumber = $customerBillingAddress->getPhoneNumber();
            $customerCompany = $customerBillingAddress->getCompany();

            $event->getPage()->addExtension('ratepay', new ArrayStruct([
                'paymentMethod' => strtolower(constant($paymentMethod->getHandlerIdentifier().'::RATEPAY_METHOD')),
                'birthday' => $customerBirthday,
                'vatId' => $customerVatId,
                'phoneNumber' => $customerPhoneNumber,
                'company' => $customerCompany,
                'accountHolder' => $customerBillingAddress->getFirstName() . " " . $customerBillingAddress->getLastName(),
                'bankAccountRequired' => $event->getSalesChannelContext()->getPaymentMethod()->getHandlerIdentifier() == DebitPaymentHandler::class,
                'isInstallmentMethod' => $event->getSalesChannelContext()->getPaymentMethod()->getHandlerIdentifier() == InstallmentPaymentHandler::class,
                'isZeroPercentInstallment' => $event->getSalesChannelContext()->getPaymentMethod()->getHandlerIdentifier() == InstallmentZeroPercentPaymentHandler::class
            ]));
        }

    }

}
