<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;


use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;

interface OperationDataWithBasket
{

    public function getItems(): array;
    public function getCurrencyCode(): string;
    public function getShippingCosts(): ?CalculatedPrice;
    public function isSendDiscountAsCartItem(): bool;
    public function isSendShippingCostsAsCartItem(): bool;

}
