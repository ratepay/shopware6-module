<?php declare(strict_types=1);

namespace Ratepay\RpayPayments\Components\Checkout\Cart;

use Ratepay\RpayPayments\Components\Checkout\Cart\Error\SecciExpiredError;
use Ratepay\RpayPayments\Components\Checkout\Service\SecciService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\CartValidatorInterface;
use Shopware\Core\Checkout\Cart\Error\ErrorCollection;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\RequestStack;

class SecciPaymentMethodValidator implements CartProcessorInterface, CartValidatorInterface
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly SecciService        $secciService,
        private readonly RequestStack        $requestStack,
    )
    {
    }


    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {

    }

    public function validate(Cart $cart, ErrorCollection $errors, SalesChannelContext $context): void
    {
        $locale = $this->requestStack->getMainRequest()->attributes->get('ratepay_saved_locale') ?? $context->getLanguageInfo()->localeCode;

        if (
            $this->systemConfigService->get('RpayPayments.config.ratepaySecciVariant') == 1
            && $this->secciService->paymentMethodRequiresSecci($context->getPaymentMethod(), $cart, $context)
            && $this->secciService->hasAttestationToken()
            && $this->secciService->getAttestationToken(
                $cart->getPrice()->getTotalPrice(),
                $context->getPaymentMethod()->getId(),
                $context->getCurrencyId(),
                $locale,
                $context->getCustomer()->getActiveBillingAddress()->getCountry()->getIso()
            ) === null
        ) {
            $errors->add(new SecciExpiredError());
        }
    }
}