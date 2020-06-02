<?php

namespace Ratepay\RatepayPayments\Components\Checkout;

use RatePAY\Service\DeviceFingerprint;
use RatePAY\Service\LanguageService;
use Ratepay\RatepayPayments\Components\DeviceFingerprint\DfpService;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

// TODO @aarends bitte in einen Ordner+Namespace ála `Subscriber`
class CheckoutEventListener implements EventSubscriberInterface
{
    // TODO @aarends siehe kommentar unten - und wendern sollten diese Constanten in den einzelnen Handlern definiert sein.
    public const RATEPAY_INVOICE_PAYMENT_HANDLER = 'handler_ratepay_invoicepaymenthandler';
    public const RATEPAY_PREPAYMENT_PAYMENT_HANDLER = 'handler_ratepay_prepaymentpaymenthandler';
    public const RATEPAY_DEBIT_PAYMENT_HANDLER = 'handler_ratepay_debitpaymenthandler';
    public const RATEPAY_INSTALLMENT_PAYMENT_HANDLER = 'handler_ratepay_installmentpaymenthandler';
    public const RATEPAY_INSTALLMENTZEROPERCENT_PAYMENT_HANDLER = 'handler_ratepay_installmentzeropercentpaymenthandler';

    /**
     * @var DfpService
     */
    protected $dfpService;

    /**
     * @var SystemConfigService
     */
    private $systemConfigService;

    public function __construct(
        DfpService $dfpService,
        SystemConfigService $systemConfigService
    )
    {
        $this->dfpService = $dfpService;
        $this->systemConfigService = $systemConfigService;
    }


    public static function getSubscribedEvents()
    {
        return [
            CheckoutConfirmPageLoadedEvent::class => 'addRatepayTemplateData',
            CheckoutOrderPlacedEvent::class => 'onCheckoutOrderPlaced'
        ];
    }

    /**
     * @param CheckoutConfirmPageLoadedEvent $event
     * @codeCoverageIgnore
     */
    public function addRatepayTemplateData(CheckoutConfirmPageLoadedEvent $event): void
    {
        $config = $this->getPluginConfiguration($event->getSalesChannelContext());

        if (!$config['ratepayDevicefingerprintingButton']) {
            return;
        }

        // TODO @aarends ist eigentlich in der falschen Component. Daher sollte dieses Codesnippet (5 Zeilen) in die entsprechende Component. (eigener Subscriber)
        if ($this->dfpService->isDfpIdAlreadyGenerated() == false) {
            $dfpHelper = new DeviceFingerprint($config['ratepayDevicefingerprintingSnippetId']);
            // TODO @aarends sollte natürlich der extension `ratepay` angehangen werden.
            $event->getPage()->addExtension('dfp', new ArrayStruct([
                'dfp' => str_replace('\"', '"', $dfpHelper->getDeviceIdentSnippet($this->dfpService->getDfpId()))
            ]));
        }

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

    /**
     * @param CheckoutOrderPlacedEvent $event
     * @codeCoverageIgnore
     */
    public function onCheckoutOrderPlaced(CheckoutOrderPlacedEvent $event): void
    {
        return; // TODO @aarends entfernen - oder wird das noch benötigt?
    }

    protected function getPluginConfiguration(?SalesChannelContext $context): array
    {
        return $this->systemConfigService->get('RatepayPayments.config', $context ? $context->getSalesChannel()->getId() : null);
    }

}
