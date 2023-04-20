<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Model;


use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileConfigSearch;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class InstallmentCalculatorContext
{

    /**
     * @var string
     */
    public const CALCULATION_TYPE_TIME = 'time';

    /**
     * @var string
     */
    public const CALCULATION_TYPE_RATE = 'rate';

    private SalesChannelContext $salesChannelContext;

    private string $calculationType;

    /**
     * @var int|float|null
     */
    private $calculationValue;

    private ?float $totalAmount = null;

    private bool $isUseCheapestRate = false;

    private ?OrderEntity $order = null;

    /**
     * @deprecated
     */
    private ?PaymentMethodEntity $paymentMethod = null;

    private ?string $paymentMethodId = null;

    private ?ProfileConfigSearch $profileConfigSearch = null;

    private ?CountryEntity $billingCountry = null;

    /**
     * @param int|float|null $calculationValue
     */
    public function __construct(
        SalesChannelContext $salesChannelContext,
        string $calculationType,
        $calculationValue = null
    )
    {
        $this->salesChannelContext = $salesChannelContext;
        $this->calculationType = $calculationType;
        
        if ($this->calculationType === self::CALCULATION_TYPE_RATE) {
            $this->calculationValue = (float)$calculationValue;
        } elseif ($this->calculationType === self::CALCULATION_TYPE_TIME) {
            $this->calculationValue = (int)$calculationValue;
        }
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

    /**
     * @return float|int
     */
    public function getCalculationValue()
    {
        return $this->calculationValue ?? 1;
    }

    /**
     * @param float|int|null $calculationValue
     */
    public function setCalculationValue($calculationValue): self
    {
        $this->calculationValue = $calculationValue;
        return $this;
    }

    public function getTotalAmount(): ?float
    {
        return $this->order !== null ? $this->order->getAmountTotal() : $this->totalAmount;
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

    public function getPaymentMethodId(): ?string
    {
        return $this->paymentMethodId;
    }

    public function setPaymentMethodId(?string $paymentMethodId): self
    {
        $this->paymentMethodId = $paymentMethodId;

        return $this;
    }

    /**
     * @return PaymentMethodEntity|null
     * @deprecated please use getPaymentMethodId
     */
    public function getPaymentMethod(): ?PaymentMethodEntity
    {
        return $this->paymentMethod ?? $this->salesChannelContext->getPaymentMethod();
    }

    /**
     * @deprecated please use setPaymentMethodId
     */
    public function setPaymentMethod(?PaymentMethodEntity $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        $this->paymentMethodId = $paymentMethod->getId();

        return $this;
    }

    public function getLanguageId(): string
    {
        return $this->order !== null ? $this->order->getLanguageId() : $this->salesChannelContext->getContext()->getLanguageId();
    }

    public function getBillingCountry(): CountryEntity
    {
        if ($this->billingCountry !== null) {
            return $this->billingCountry;
        }

        if ($this->order !== null) {
            return $this->order->getAddresses()->get($this->order->getBillingAddressId())->getCountry();
        }

        return $this->salesChannelContext->getCustomer()->getActiveBillingAddress()->getCountry();
    }

    public function isUseCheapestRate(): bool
    {
        return $this->isUseCheapestRate;
    }

    public function setIsUseCheapestRate(bool $isUseCheapestRate): self
    {
        $this->isUseCheapestRate = $isUseCheapestRate;

        return $this;
    }

    public function getProfileConfigSearch(): ?ProfileConfigSearch
    {
        return $this->profileConfigSearch;
    }

    public function setProfileConfigSearch(?ProfileConfigSearch $profileConfigSearch): self
    {
        $this->profileConfigSearch = $profileConfigSearch;

        return $this;
    }

    public function setBillingCountry(CountryEntity $billingCountry): self
    {
        $this->billingCountry = $billingCountry;

        return $this;
    }

}
