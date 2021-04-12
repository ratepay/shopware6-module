<?php


namespace Ratepay\RpayPayments\Components\ProfileConfig\Event;


use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class CreateProfileConfigCriteriaEvent
{
    /**
     * @var Criteria
     */
    private $criteria;
    /**
     * @var string
     */
    private $paymentMethodId;
    /**
     * @var string
     */
    private $billingCountryIso;
    /**
     * @var string
     */
    private $shippingCountryIso;
    /**
     * @var string
     */
    private $salesChannelId;
    /**
     * @var string
     */
    private $currencyIso;
    /**
     * @var bool
     */
    private $differentAddresses;
    /**
     * @var Context
     */
    private $context;

    public function __construct(
        Criteria $criteria,
        string $paymentMethodId,
        string $billingCountryIso,
        string $shippingCountryIso,
        string $salesChannelId,
        string $currencyIso,
        bool $differentAddresses,
        Context $context
    )
    {
        $this->criteria = $criteria;
        $this->paymentMethodId = $paymentMethodId;
        $this->billingCountryIso = $billingCountryIso;
        $this->shippingCountryIso = $shippingCountryIso;
        $this->salesChannelId = $salesChannelId;
        $this->currencyIso = $currencyIso;
        $this->differentAddresses = $differentAddresses;
        $this->context = $context;
    }

    /**
     * @return Criteria
     */
    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    /**
     * @return string
     */
    public function getPaymentMethodId(): string
    {
        return $this->paymentMethodId;
    }

    /**
     * @return string
     */
    public function getBillingCountryIso(): string
    {
        return $this->billingCountryIso;
    }

    /**
     * @return string
     */
    public function getShippingCountryIso(): string
    {
        return $this->shippingCountryIso;
    }

    /**
     * @return string
     */
    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    /**
     * @return string
     */
    public function getCurrencyIso(): string
    {
        return $this->currencyIso;
    }

    /**
     * @return bool
     */
    public function isDifferentAddresses(): bool
    {
        return $this->differentAddresses;
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }
}
