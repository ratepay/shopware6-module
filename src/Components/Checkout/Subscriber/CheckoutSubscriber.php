<?php

namespace Ratepay\RatepayPayments\Components\Checkout\Subscriber;

use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use  Ratepay\RatepayPayments\Components\PaymentHandler\PaymentHelper;

class CheckoutSubscriber implements EventSubscriberInterface
{
    /** @var PaymentHelper */
    private $paymentHelper;

    public function __construct(
        PaymentHelper $paymentHelper
    )
    {
        $this->paymentHelper = $paymentHelper;
    }

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
        /* Get customer data for checkout form */
        $customerBirthday = $event->getSalesChannelContext()->getCustomer()->getBirthday();
        $customerBillingAddress = $event->getSalesChannelContext()->getCustomer()->getActiveBillingAddress();
        $customerVatId = $customerBillingAddress->getVatId();
        $customerPhoneNumber = $customerBillingAddress->getPhoneNumber();
        $customerCompany = $customerBillingAddress->getCompany();

        $ratepayMethodIsSelected = $this->paymentHelper->isRatepayPaymentsSelected($event->getSalesChannelContext());
        $bankAccountRequired = $this->paymentHelper->bankAccountRequired($event->getSalesChannelContext());
        $isInstallmentMethod = $this->paymentHelper->isInstallmentMethod($event->getSalesChannelContext());
        $isZeroPercentInstallment = $this->paymentHelper->isZeroPercentInstallment($event->getSalesChannelContext());

        $event->getPage()->addExtension('ratepay', new ArrayStruct([
            'ratepayMethodIsSelected' => $ratepayMethodIsSelected,
            'birthday' => $customerBirthday,
            'vatId' => $customerVatId,
            'phoneNumber' => $customerPhoneNumber,
            'company' => $customerCompany,
            'bankAccountRequired' => $bankAccountRequired,
            'isInstallmentMethod' => $isInstallmentMethod,
            'isZeroPercentInstallment' => $isZeroPercentInstallment
        ]));

    }

}
