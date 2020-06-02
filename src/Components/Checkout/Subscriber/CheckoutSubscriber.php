<?php

namespace Ratepay\RatepayPayments\Components\Checkout\Subscriber;

use RatePAY\Service\LanguageService;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutSubscriber implements EventSubscriberInterface
{
    // TODO @aarends siehe kommentar unten - und wendern sollten diese Constanten in den einzelnen Handlern definiert sein.
    public const RATEPAY_INVOICE_PAYMENT_HANDLER = 'handler_ratepay_invoicepaymenthandler';
    public const RATEPAY_PREPAYMENT_PAYMENT_HANDLER = 'handler_ratepay_prepaymentpaymenthandler';
    public const RATEPAY_DEBIT_PAYMENT_HANDLER = 'handler_ratepay_debitpaymenthandler';
    public const RATEPAY_INSTALLMENT_PAYMENT_HANDLER = 'handler_ratepay_installmentpaymenthandler';
    public const RATEPAY_INSTALLMENTZEROPERCENT_PAYMENT_HANDLER = 'handler_ratepay_installmentzeropercentpaymenthandler';

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
        /* Get translations from SDK */
        $ratepayLanguageService = new LanguageService();
        $ratepayLocaleArray = $ratepayLanguageService->getArray();

        /* Get customer data for checkout form */
        $customerBirthday = $event->getSalesChannelContext()->getCustomer()->getBirthday();
        $customerBillingAddress = $event->getSalesChannelContext()->getCustomer()->getActiveBillingAddress();
        $customerVatId = $customerBillingAddress->getVatId();
        $customerPhoneNumber = $customerBillingAddress->getPhoneNumber();

        $event->getPage()->addExtension('ratepay', new ArrayStruct([
            'birthday' => $customerBirthday,
            'vatId' => $customerVatId,
            'phoneNumber' => $customerPhoneNumber,
            'allowedPaymentHandler' => array( // TODO @aarends siehe kommentar im template: sollten wir dementsprechend nicht mehr benötigen. allgemein: es wäre schön, so wenig wie möglich Stellen zu haben, wo wir eine Auflistung aller Zahlungsarten haben, um in Zukunft ggfs. weitere Zahlungsarten hinzufügen können.
                self::RATEPAY_PREPAYMENT_PAYMENT_HANDLER,
                self::RATEPAY_INVOICE_PAYMENT_HANDLER,
                self::RATEPAY_DEBIT_PAYMENT_HANDLER,
                self::RATEPAY_INSTALLMENT_PAYMENT_HANDLER,
                self::RATEPAY_INSTALLMENTZEROPERCENT_PAYMENT_HANDLER
            ),
            'locale' => $ratepayLocaleArray
        ]));
    }

}
