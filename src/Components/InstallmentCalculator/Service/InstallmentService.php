<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Service;

use Ratepay\RpayPayments\Components\Checkout\Event\RatepayPaymentFilterEvent;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Model\InstallmentBuilder;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Model\InstallmentCalculatorContext;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Util\PlanHasher;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\ProfileConfigService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\TransactionIdService;
use RatePAY\Service\LanguageService;
use RuntimeException;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class InstallmentService
{
    protected ProfileConfigService $profileConfigService;

    private CartService $cartService;

    private EntityRepositoryInterface $languageRepository;

    private TransactionIdService $transactionIdService;

    private EventDispatcherInterface $eventDispatcher;

    private array $_translationCache = [];

    public const CALCULATION_TYPE_TIME = 'time';

    public const CALCULATION_TYPE_RATE = 'rate';


    public function __construct(
        CartService $cartService,
        EntityRepositoryInterface $languageRepository,
        ProfileConfigService $profileConfigService,
        TransactionIdService $transactionIdService,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->cartService = $cartService;
        $this->languageRepository = $languageRepository;
        $this->profileConfigService = $profileConfigService;
        $this->transactionIdService = $transactionIdService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getInstallmentPlanData(InstallmentCalculatorContext $calcContext): array
    {
        $salesChannelContext = $calcContext->getSalesChannelContext();

        $installmentBuilders = $this->getInstallmentBuilders($calcContext);

        if ($calcContext->getTotalAmount() === null) {
            $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);
            $cartTotalAmount = $cart->getPrice()->getTotalPrice();
        } else {
            $cartTotalAmount = $calcContext->getTotalAmount();
        }
        if (!count($installmentBuilders)) {
            throw new \RuntimeException('InstallmentBuilder can not be created.');
        }

        $matchedBuilder = null;
        $amountJsons = $amountBuilders = [];

        foreach ($installmentBuilders as $installmentBuilder) {
            if ($calcContext->getCalculationType() === self::CALCULATION_TYPE_TIME) {
                $matchedBuilder = $installmentBuilder;
                break; // the first should be fine.
            }

            if ($calcContext->getCalculationType() === self::CALCULATION_TYPE_RATE) {
                // collect all rates for all available plans
                $_planJson = $installmentBuilder->getInstallmentPlanAsJson($cartTotalAmount, $calcContext->getCalculationType(), $calcContext->getCalculationValue());
                $planArray = json_decode($_planJson, true);
                $amountBuilders[$planArray['rate']] = $installmentBuilder;
                $amountJsons[$planArray['rate']] = $_planJson;
            }
        }

        if ($calcContext->getCalculationType() === self::CALCULATION_TYPE_RATE) {
            // find the best matching for the given monthly rate and the available rates from the calculated plans
            $closestAmount = null;
            $availableMonthlyRates = array_keys($amountBuilders);
            sort($availableMonthlyRates);
            foreach ($availableMonthlyRates as $availableMonthlyRate) {
                if ($closestAmount === null || abs($calcContext->getCalculationValue() - $closestAmount) > abs($availableMonthlyRate - $calcContext->getCalculationValue())) {
                    $closestAmount = $availableMonthlyRate;
                } else if ($availableMonthlyRate > $calcContext->getCalculationValue()) {
                    // if it is not a match, and the calculated rate is already higher than the given value,
                    // we can cancel the loop, cause every higher values will not match, too.
                    break;
                }
            }
            $matchedBuilder = $amountBuilders[$closestAmount];
            $planJson = $amountJsons[$closestAmount];
        }

        if ($matchedBuilder) {
            /** @var \Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity $paymentConfig */
            $paymentConfig = $matchedBuilder->getProfileConfig()->getPaymentMethodConfigs()->filterByMethod($salesChannelContext->getPaymentMethod()->getId())->first();

            $json = $planJson ?? $matchedBuilder->getInstallmentPlanAsJson($cartTotalAmount, $calcContext->getCalculationType(), $calcContext->getCalculationValue());
            $data = json_decode($json, true);
            $data['hash'] = PlanHasher::hashPlan($data);
            $data['profileUuid'] = $matchedBuilder->getProfileConfig()->getId();
            $data['payment'] = [
                'default' => $paymentConfig->getInstallmentConfig()->getIsDebitAllowed() ? 'DIRECT-DEBIT' : 'BANK-TRANSFER',
                'bankTransferAllowed' => $paymentConfig->getInstallmentConfig()->getIsBankTransferAllowed(),
                'directDebitAllowed' => $paymentConfig->getInstallmentConfig()->getIsDebitAllowed(),
            ];
            return $data;
        }

        throw new \RuntimeException('We were not able to calculate the installment rate.');
    }

    /**
     * @param \Ratepay\RpayPayments\Components\InstallmentCalculator\Model\InstallmentCalculatorContext $context
     * @return array<\Ratepay\RpayPayments\Components\InstallmentCalculator\Model\InstallmentBuilder>
     */
    protected function getInstallmentBuilders(InstallmentCalculatorContext $context): array
    {
        $salesChannelContext = $context->getSalesChannelContext();

        if ($context->getOrder()) {
            $profileConfigs = $this->profileConfigService->getProfileConfigByOrderEntity($context->getOrder(), $context->getPaymentMethod()->getId(), $context->getSalesChannelContext()->getContext(), false);
        } else {
            $profileConfigs = $this->profileConfigService->getProfileConfigBySalesChannel($salesChannelContext, $context->getPaymentMethod()->getId(), false);
        }

        if ($profileConfigs === null) {
            throw new RuntimeException('no profile id found');
        }

        $installmentBuilders = [];
        foreach ($profileConfigs as $profileConfig) {
            /** @var \Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity $paymentMethodConfig */
            $paymentMethodConfig = $profileConfig->getPaymentMethodConfigs()->filterByMethod($salesChannelContext->getPaymentMethod()->getId())->first();

            // we need to filter the profile configs here again (also during payment method selection), cause the installment methods can have more than one profile config
            $filterEvent = new RatepayPaymentFilterEvent($context->getPaymentMethod(), $profileConfig, $paymentMethodConfig, $salesChannelContext, $context->getOrder());
            /** @var RatepayPaymentFilterEvent $filterEvent */
            $filterEvent = $this->eventDispatcher->dispatch($filterEvent);
            if ($filterEvent->isAvailable() === false) {
                continue;
            }

            if (($context->getCalculationType() === self::CALCULATION_TYPE_TIME) && !in_array((int)$context->getCalculationValue(), $paymentMethodConfig->getInstallmentConfig()->getAllowedMonths(), true)) {
                // filter the zero percent installment configs for the allowed months
                continue;
            }

            $installmentBuilders[] = new InstallmentBuilder($profileConfig, $context->getLanguageId(), $context->getBillingCountry()->getIso());
        }

        return $installmentBuilders;
    }

    public function getInstallmentCalculatorData(SalesChannelContext $salesChannelContext): array
    {
        $installmentBuilders = $this->getInstallmentBuilders(new InstallmentCalculatorContext($salesChannelContext, '', ''));
        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        $data = [];
        foreach ($installmentBuilders as $installmentBuilder) {
            $json = $installmentBuilder->getInstallmentCalculatorAsJson($cart->getPrice()->getTotalPrice());
            $configuratorData = json_decode($json, true);

            if (count($data) === 0) {
                $data = $configuratorData;
            }
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $data['rp_allowedMonths'] = array_merge($data['rp_allowedMonths'], $configuratorData['rp_allowedMonths']);
        }
        $data['rp_allowedMonths'] = array_unique($data['rp_allowedMonths']);
        sort($data['rp_allowedMonths']);

        $data['defaults']['type'] = self::CALCULATION_TYPE_TIME;
        $data['defaults']['value'] = $data['rp_allowedMonths'][0];

        return $data;
    }

    public function getTranslations(SalesChannelContext $salesChannelContext): array
    {
        $langId = $salesChannelContext->getContext()->getLanguageId();
        if (isset($this->_translationCache[$langId]) === false) {
            $languageCriteria = new Criteria([$salesChannelContext->getContext()->getLanguageId()]);
            $languageCriteria->addAssociation('locale');
            $language = $this->languageRepository->search(
                $languageCriteria,
                $salesChannelContext->getContext()
            )->first();

            $languageCode = strtoupper(explode('-', $language->getLocale()->getCode())[0]);
            $translations = (new LanguageService($languageCode))->getArray();

            $this->_translationCache[$langId] = $translations;
        }

        return $this->_translationCache[$langId];
    }

    public function getInstallmentPlanTwigVars(InstallmentCalculatorContext $context)
    {
        $installmentPlan = $this->getInstallmentPlanData($context);

        $transactionId = $this->transactionIdService->getTransactionId(
            $context->getSalesChannelContext(),
            $context->getOrder() ? TransactionIdService::PREFIX_ORDER . $context->getOrder()->getId() : TransactionIdService::PREFIX_CART,
            $installmentPlan['profileUuid']
        );

        return [
            'translations' => $this->getTranslations($context->getSalesChannelContext()),
            'plan' => $installmentPlan,
            'transactionId' => $transactionId,
        ];
    }
}
