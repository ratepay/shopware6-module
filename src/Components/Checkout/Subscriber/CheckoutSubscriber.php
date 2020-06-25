<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Subscriber;

use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => ['addRatepayTemplateData', 900]
        ];
    }

    /**
     * @param CheckoutConfirmPageLoadedEvent $event
     * @codeCoverageIgnore
     */
    public function addRatepayTemplateData(CheckoutConfirmPageLoadedEvent $event): void
    {
        $paymentMethod = $event->getSalesChannelContext()->getPaymentMethod();
        if (strpos($paymentMethod->getHandlerIdentifier(), 'RatepayPayments') !== false) {
            /* Get customer data for checkout form */
            $customerBirthday = $event->getSalesChannelContext()->getCustomer()->getBirthday();
            $customerBillingAddress = $event->getSalesChannelContext()->getCustomer()->getActiveBillingAddress();
            $customerVatId = $customerBillingAddress->getVatId();
            $customerPhoneNumber = $customerBillingAddress->getPhoneNumber();
            $customerCompany = $customerBillingAddress->getCompany();

            $extension = $event->getPage()->getExtension('ratepay') ?? new ArrayStruct();
            $extension->set('paymentMethod', strtolower(constant($paymentMethod->getHandlerIdentifier() . '::RATEPAY_METHOD')));
            $extension->set('birthday', $customerBirthday);
            $extension->set('vatId', $customerVatId);
            $extension->set('phoneNumber', $customerPhoneNumber);
            $extension->set('company', $customerCompany);
            $extension->set('accountHolder', $customerBillingAddress->getFirstName() . " " . $customerBillingAddress->getLastName());
            $event->getPage()->addExtension('ratepay', $extension);
        }

    }

}
