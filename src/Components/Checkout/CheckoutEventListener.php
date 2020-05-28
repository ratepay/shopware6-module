<?php

namespace Ratepay\RatepayPayments\Components\Checkout;

use RatePAY\Service\DeviceFingerprint;
use Ratepay\RatepayPayments\Components\DeviceFingerprint\DfpService;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CheckoutEventListener implements EventSubscriberInterface
{
    public const RATEPAY_INVOICE_PAYMENT_HANDLER = 'handler_ratepay_invoicepaymenthandler';
    public const RATEPAY_PREPAYMENT_PAYMENT_HANDLER = 'handler_ratepay_prepaymentpaymenthandler';
    public const RATEPAY_DEBIT_PAYMENT_HANDLER = 'handler_ratepay_debitpaymenthandler';

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
            CheckoutConfirmPageLoadedEvent::class => 'addRatepayTemplateData'
        ];
    }

    /**
     * @param CheckoutConfirmPageLoadedEvent $event
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     * @codeCoverageIgnore
     */
    public function addRatepayTemplateData(CheckoutConfirmPageLoadedEvent $event): void
    {
        $config = $this->getPluginConfiguration($event->getSalesChannelContext());

        if (!$config['ratepayDevicefingerprintingButton']) {
            return;
        }

        if ($this->dfpService->isDfpIdAlreadyGenerated() == false) {
            $dfpHelper = new DeviceFingerprint($config['ratepayDevicefingerprintingSnippetId']);
            $event->getPage()->addExtension('dfp', new ArrayStruct([
                'dfp' => str_replace('\"', '"', $dfpHelper->getDeviceIdentSnippet($this->dfpService->getDfpId()))
            ]));
        }

        $customerBirthday = $event->getSalesChannelContext()->getCustomer()->getBirthday();
        $customerBillingAddress = $event->getSalesChannelContext()->getCustomer()->getActiveBillingAddress();
        $customerVatId = $customerBillingAddress->getVatId();
        $customerPhoneNumber = $customerBillingAddress->getPhoneNumber();

        $event->getPage()->addExtension('ratepay', new ArrayStruct([
            'birthday' => $customerBirthday,
            'vatId' => $customerVatId,
            'phoneNumber' => $customerPhoneNumber,
            'allowedPaymentHandler' => array(
                self::RATEPAY_PREPAYMENT_PAYMENT_HANDLER,
                self::RATEPAY_INVOICE_PAYMENT_HANDLER,
                self::RATEPAY_DEBIT_PAYMENT_HANDLER
            )
        ]));

    }


    protected function getPluginConfiguration(?SalesChannelContext $context): array
    {
        return $this->systemConfigService->get( 'RatepayPayments.config', $context ? $context->getSalesChannel()->getId() : null);
    }

}
