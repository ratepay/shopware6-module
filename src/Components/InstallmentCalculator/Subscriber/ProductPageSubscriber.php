<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Subscriber;

use Exception;
use Psr\Log\LoggerInterface;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Model\InstallmentCalculatorContext;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Service\InstallmentService;
use Ratepay\RpayPayments\Components\ProfileConfig\Dto\ProfileConfigSearch;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use RuntimeException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductPageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SystemConfigService $configService,
        private readonly InstallmentService $installmentService,
        private readonly EntityRepository $countryRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'addRatepayData',
        ];
    }

    public function addRatepayData(ProductPageLoadedEvent $event): void
    {
        if (!$this->getConfig('Enabled', 'boolean')) {
            return;
        }

        $billingCountyId = $this->getConfig('BillingCountry');
        $billingCountry = $this->countryRepository->search(new Criteria([$billingCountyId]), $event->getContext())->first();
        $shippingCountyId = $this->getConfig('ShippingCountry');
        $shippingCountry = $this->countryRepository->search(new Criteria([$shippingCountyId]), $event->getContext())->first();
        if (!$billingCountry instanceof CountryEntity || !$shippingCountry instanceof CountryEntity) {
            return;
        }

        $currency = $event->getSalesChannelContext()->getCurrency();

        $paymentMethodId = $this->getConfig('PaymentMethod');

        $product = $event->getPage()->getProduct();

        // this is a little bit tricky. we need to support the following cases: normal price, configurable price, advanced/tier pricing
        // sometimes `getCalculatedPrices` does not return a price. so we use the fallback `getCalculatedCheapestPrice`
        // the fallback `getCalculatedCheapestPrice` can not be always used because it will not return the correct price for configurable prices
        $productPriceObject = $product->getCalculatedPrices()->first();
        $productPriceObject ??= $product->getCalculatedCheapestPrice();

        $productPrice = $productPriceObject->getUnitPrice();

        $profileConfigSearch = (new ProfileConfigSearch())
            ->setPaymentMethodId($paymentMethodId)
            ->setBillingCountryCode($billingCountry->getIso())
            ->setShippingCountryCode($shippingCountry->getIso())
            ->setSalesChannelId($event->getSalesChannelContext()->getSalesChannelId())
            ->setCurrency($currency->getIsoCode())
            ->setIsB2b(false) // Installments are not available for B2B customers
            ->setIsAdminOrder(false)
            ->setTotalAmount($productPrice);

        // this is a little bit hacky, but it just works ;-)
        // we simulate an installment calculation with a requested monthly rate of zero.
        // The service will automatically find the best rate for the request
        $calcContext = (new InstallmentCalculatorContext(
            $event->getSalesChannelContext(),
            InstallmentCalculatorContext::CALCULATION_TYPE_RATE,
            0
        ));
        $calcContext->setPaymentMethodId($paymentMethodId);
        $calcContext->setTotalAmount($profileConfigSearch->getTotalAmount());
        $calcContext->setProfileConfigSearch($profileConfigSearch);
        $calcContext->setBillingCountry($billingCountry);

        try {
            $plan = $this->installmentService->calculateInstallmentOffline($calcContext);
            $event->getPage()->addExtension('ratepayInstallment', new ArrayStruct([
                'isZeroPercent' => $plan->getBuilder()->getMethodConfig()->getInstallmentConfig()->getDefaultInterestRate() === 0.0,
                'monthCount' => $plan->getMonthCount(),
                'monthlyRate' => $plan->getMonthlyRate(),
            ]));
        } catch (ProfileNotFoundException) {
            // we do not log this error, because the error is expected. e.g. if the amount is too low for installments
        } catch (Exception $exception) {
            $this->logger->error('Error during display of installment information on detail page: ' . $exception->getMessage(), [
                'product_uuid' => $product->getId(),
                'product_number' => $product->getProductNumber(),
                'product_name' => $product->getName(),
                'product_price' => $calcContext->getTotalAmount(),
                'billing_country' => $calcContext->getProfileConfigSearch()->getBillingCountryCode(),
                'shipping_country' => $calcContext->getProfileConfigSearch()->getShippingCountryCode(),
                'b2b_enabled' => $calcContext->getProfileConfigSearch()->isB2b(),
            ]);
        }
    }

    private function getConfig(string $key, string $type = 'string'): bool|float|int|string
    {
        $key = 'RpayPayments.config.productInstallmentCalculator' . $key;

        return match ($type) {
            'string' => $this->configService->getString($key),
            'float' => $this->configService->getFloat($key),
            'integer' => $this->configService->getInt($key),
            'boolean' => $this->configService->getBool($key),
            default => throw new RuntimeException('type ' . $type . ' is not valid'),
        };
    }
}
