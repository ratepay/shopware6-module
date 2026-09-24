<?php declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;

use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Currency\CurrencyEntity;

class SecciRequestData extends AbstractRequestData implements OperationDataWithCart
{

    public const DELIVERY_METHOD_EMAIL = 'EMAIL';
    public const DELIVERY_METHOD_PDF = 'PDF';
    public const ACTION_DOWNLOAD = 'DOWNLOAD';

    public function __construct(
        private readonly string              $deliveryMethod,
        private readonly ?string             $email,
        private readonly string              $languageIso,
        private readonly string              $countryCode,
        private readonly Cart                $cart,
        private readonly PaymentMethodEntity $paymentMethod,
        private readonly CurrencyEntity      $currency,
        private readonly string              $taxState,
        ProfileConfigEntity                  $profileConfig,
        Context                              $context,
    )
    {
        parent::__construct($context);
        $this->setProfileConfig($profileConfig);
    }

    public function getDeliveryMethod(): string
    {
        return $this->deliveryMethod;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getLanguageIso(): string
    {
        return $this->languageIso;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getPaymentMethod(): PaymentMethodEntity
    {
        return $this->paymentMethod;
    }

    public function getCurrency(): CurrencyEntity
    {
        return $this->currency;
    }

    public function getTaxState(): string
    {
        return $this->taxState;
    }
}