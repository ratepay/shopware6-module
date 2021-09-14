<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Model;


use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class InstallmentCalculatorContext
{

    private SalesChannelContext $salesChannelContext;

    private string $calculationType;

    private string $calculationValue;

    private ?float $totalAmount = null;

    private ?OrderEntity $order = null;
    private ?PaymentMethodEntity $paymentMethod = null;

    public function __construct(
        SalesChannelContext $salesChannelContext,
        string $calculationType,
        string $calculationValue
    )
    {
        $this->salesChannelContext = $salesChannelContext;
        $this->calculationType = $calculationType;
        $this->calculationValue = $calculationValue;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function setSalesChannelContext(SalesChannelContext $salesChannelContext): self
    {
        $this->salesChannelContext = $salesChannelContext;
        return $this;
    }

    public function getCalculationType(): string
    {
        return $this->calculationType;
    }

    public function setCalculationType(string $calculationType): self
    {
        $this->calculationType = $calculationType;
        return $this;
    }

    public function getCalculationValue(): string
    {
        return $this->calculationValue;
    }

    public function setCalculationValue(string $calculationValue): self
    {
        $this->calculationValue = $calculationValue;
        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->order ? $this->order->getAmountTotal() : $this->totalAmount;
    }

    public function setTotalAmount(?float $totalAmount): self
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function getOrder(): ?OrderEntity
    {
        return $this->order;
    }

    public function setOrder(?OrderEntity $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getPaymentMethod(): ?PaymentMethodEntity
    {
        return $this->paymentMethod ?? $this->salesChannelContext->getPaymentMethod();
    }

    public function setPaymentMethod(?PaymentMethodEntity $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function getLanguageId()
    {
        return $this->order ? $this->order->getLanguageId() : $this->salesChannelContext->getContext()->getLanguageId();
    }

    public function getBillingCountry(): CountryEntity
    {
        if ($this->order) {
            return $this->order->getAddresses()->get($this->order->getBillingAddressId())->getCountry();
        }

        return $this->salesChannelContext->getCustomer()->getActiveBillingAddress()->getCountry();
    }

}
