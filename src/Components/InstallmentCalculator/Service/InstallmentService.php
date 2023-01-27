<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Service;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Exception;
use Monolog\Logger;
use RatePAY\Exception\RequestException;
use RatePAY\ModelBuilder;
use Ratepay\RpayPayments\Components\Checkout\Event\RatepayPaymentFilterEvent;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Model\InstallmentBuilder;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Model\InstallmentCalculatorContext;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Model\OfflineInstallmentCalculatorResult;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Util\PlanHasher;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodInstallmentEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileByOrderEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileSearchService;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileBySalesChannelContext;
use Ratepay\RpayPayments\Components\RatepayApi\Exception\TransactionIdFetchFailedException;
use Ratepay\RpayPayments\Components\RatepayApi\Service\TransactionIdService;
use Ratepay\RpayPayments\Util\PaymentFirstday;
use RatePAY\Service\LanguageService;
use RatePAY\Service\OfflineInstallmentCalculation;
use RuntimeException;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class InstallmentService
{
    private CartService $cartService;

    private EntityRepository $languageRepository;

    private TransactionIdService $transactionIdService;

    private EventDispatcherInterface $eventDispatcher;

    /**
     * the interface has been deprecated, but shopware is using the Interface in a decorator for the repository.
     * so it will crash, if we are only using EntityRepository, cause an object of the decorator got injected into the constructor.
     *
     * After Shopware has removed the decorator, we can replace this by a normal definition
     * @var EntityRepository|\Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface
     * TODO remove comment on Shopware Version 6.5.0.0 & readd type int & change constructor argument type
     */
    private $paymentMethodRepository;

    private Logger $logger;

    private ProfileSearchService $profileSearchService;

    private ProfileByOrderEntity $profileByOrderEntity;

    private ProfileBySalesChannelContext $profileBySalesChannelContext;

    private array $_translationCache = [];

    /**
     * @deprecated
     * @var string
     */
    public const CALCULATION_TYPE_TIME = InstallmentCalculatorContext::CALCULATION_TYPE_TIME;

    /**
     * @deprecated
     * @var string
     */
    public const CALCULATION_TYPE_RATE = InstallmentCalculatorContext::CALCULATION_TYPE_RATE;


    public function __construct(
        CartService $cartService,
        EntityRepository $languageRepository,
        ProfileSearchService $profileSearchService,
        ProfileByOrderEntity $profileByOrderEntity,
        ProfileBySalesChannelContext $profileBySalesChannelContext,
        TransactionIdService $transactionIdService,
        EventDispatcherInterface $eventDispatcher,
        $paymentMethodRepository,
        Logger $logger
    )
    {
        $this->cartService = $cartService;
        $this->languageRepository = $languageRepository;
        $this->transactionIdService = $transactionIdService;
        $this->eventDispatcher = $eventDispatcher;
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->logger = $logger;
        $this->profileByOrderEntity = $profileByOrderEntity;
        $this->profileBySalesChannelContext = $profileBySalesChannelContext;
        $this->profileSearchService = $profileSearchService;
    }

    public function getInstallmentPlanData(InstallmentCalculatorContext $context): array
    {
        if ($context->getTotalAmount() === null) {
            $salesChannelContext = $context->getSalesChannelContext();
            $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);
            $context->setTotalAmount($cart->getPrice()->getTotalPrice());
        }

        $result = $this->calculateInstallmentOffline($context);

        if ($matchedBuilder = $result->getBuilder()) {
            /** @var ProfileConfigMethodEntity $paymentConfig */
            $paymentConfig = $matchedBuilder->getProfileConfig()->getPaymentMethodConfigs()->filterByMethod($context->getPaymentMethodId())->first();

            try {
                $panJson = $matchedBuilder->getInstallmentPlanAsJson(
                    $context->getTotalAmount(),
                    $context->getCalculationType(),
                    $context->getCalculationValue()
                );
                $matchedPlan = json_decode($panJson, true, 512, JSON_THROW_ON_ERROR);

                if (is_array($matchedPlan)) {
                    $matchedPlan['hash'] = PlanHasher::hashPlan($matchedPlan);
                    $matchedPlan['profileUuid'] = $matchedBuilder->getProfileConfig()->getId();
                    $matchedPlan['payment'] = [
                        'default' => $paymentConfig->getInstallmentConfig()->getDefaultPaymentType(),
                        'bankTransferAllowed' => $paymentConfig->getInstallmentConfig()->getIsBankTransferAllowed(),
                        'directDebitAllowed' => $paymentConfig->getInstallmentConfig()->getIsDebitAllowed(),
                    ];

                    return $matchedPlan;
                }
            } catch (RequestException $requestException) {
                $this->logger->error('Error during fetching installment plan: ' . $requestException->getMessage(), [
                    'total_amount' => $context->getTotalAmount(),
                    'calculation_type' => $context->getCalculationType(),
                    'calculation_value' => $context->getCalculationValue(),
                    'profile_id' => $matchedBuilder->getProfileConfig()->getProfileId()
                ]);

                throw $requestException;
            }
        }

        throw new RuntimeException('We were not able to calculate the installment rate.');
    }

    /**
     * @param InstallmentCalculatorContext $context
     * @return array<InstallmentBuilder>
     */
    protected function getInstallmentBuilders(InstallmentCalculatorContext $context): array
    {
        if (!$context->getPaymentMethodId()) {
            throw new RuntimeException('please set payment method');
        }

        $salesChannelContext = $context->getSalesChannelContext();
        $shopwareContext = $context->getSalesChannelContext()->getContext();

        if ($context->getProfileConfigSearch() !== null) {
            $profileConfigs = $this->profileSearchService->search($context->getProfileConfigSearch());
        } else {
            $searchService = $context->getOrder() !== null ? $this->profileByOrderEntity : $this->profileBySalesChannelContext;
            $profileConfigs = $searchService->search(
                $searchService->createSearchObject($context->getOrder() ?? $salesChannelContext)
                    ->setPaymentMethodId($context->getPaymentMethodId())
            );
        }

        if ($profileConfigs->count() === 0) {
            throw new ProfileNotFoundException();
        }

        // load payment method for RatepayPaymentFilterEvent
        $paymentMethod = $this->paymentMethodRepository->search(new Criteria([$context->getPaymentMethodId()]), $shopwareContext)->first();
        if (!$paymentMethod) {
            throw new RuntimeException('Payment method ' . $context->getPaymentMethodId() . ' does not exist');
        }

        $installmentBuilders = [];
        foreach ($profileConfigs->getElements() as $profileConfig) {
            /** @var ProfileConfigMethodEntity $paymentMethodConfig */
            $paymentMethodConfig = $profileConfig->getPaymentMethodConfigs()->filterByMethod($context->getPaymentMethodId())->first();

            // we need to filter the profile configs here again (also during payment method selection), cause the installment methods can have more than one profile config
            $filterEvent = new RatepayPaymentFilterEvent($paymentMethod, $profileConfig, $paymentMethodConfig, $salesChannelContext, $context->getOrder());
            /** @var RatepayPaymentFilterEvent $filterEvent */
            $filterEvent = $this->eventDispatcher->dispatch($filterEvent);
            if (!$filterEvent->isAvailable()) {
                continue;
            }

            if (($context->getCalculationType() === self::CALCULATION_TYPE_TIME) && !in_array((int)$context->getCalculationValue(), $paymentMethodConfig->getInstallmentConfig()->getAllowedMonths(), true)) {
                // filter the zero percent installment configs for the allowed months
                continue;
            }

            $installmentBuilders[] = new InstallmentBuilder($profileConfig, $paymentMethodConfig, $context->getLanguageId(), $context->getBillingCountry()->getIso());
        }

        return $installmentBuilders;
    }

    public function getInstallmentCalculatorData(InstallmentCalculatorContext $context): array
    {
        $installmentBuilders = $this->getInstallmentBuilders($context);

        if ($installmentBuilders === []) {
            throw new Exception('No installment builder where found');
        }

        $data = [];
        foreach ($installmentBuilders as $installmentBuilder) {
            $json = $installmentBuilder->getInstallmentCalculatorAsJson($context->getTotalAmount());
            $configuratorData = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            if ((is_countable($data) ? count($data) : 0) === 0) {
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

    public function calculateInstallmentOffline(InstallmentCalculatorContext $context): ?OfflineInstallmentCalculatorResult
    {
        $installmentBuilders = $this->getInstallmentBuilders($context);

        if ($installmentBuilders === []) {
            throw new Exception('No installment builder where found');
        }

        /**
         * @var array{0: InstallmentBuilder, 0: int} $amountBuilders
         */
        $amountBuilders = [];

        foreach ($installmentBuilders as $installmentBuilder) {
            $installmentConfig = $installmentBuilder->getMethodConfig()->getInstallmentConfig();

            if ($context->getCalculationType() === InstallmentCalculatorContext::CALCULATION_TYPE_TIME) {
                $rate = $this->calculateMonthlyRate($context->getTotalAmount(), $installmentConfig, $context->getCalculationValue());
                if ($rate >= $installmentConfig->getRateMin()) {
                    $amountBuilders[$rate] = [$installmentBuilder, $context->getCalculationValue()];
                    break; // an explicit month was requested and there is a result. It is not required to compare it with other profiles
                }
            }

            if ($context->getCalculationType() === InstallmentCalculatorContext::CALCULATION_TYPE_RATE) {
                // collect all rates for all available plans
                foreach ($installmentConfig->getAllowedMonths() as $month) {
                    $rate = $this->calculateMonthlyRate($context->getTotalAmount(), $installmentConfig, $month);
                    if ($rate >= $installmentConfig->getRateMin()) {
                        $amountBuilders[(string)$rate] = [$installmentBuilder, $month];
                        // we will NOT break the parent foreach, cause there might be a better result (of a builder) which is nearer to the requested value than the current.
                    }
                }
            }
        }

        $count = count($amountBuilders);
        if ($count === 0) {
            return null;
        } elseif ($count > 1) {
            // find the best matching for the given monthly rate and the available rates from the calculated plans
            $monthlyRate = null;
            $availableMonthlyRates = array_keys($amountBuilders);
            sort($availableMonthlyRates);
            if ($context->isUseCheapestRate()) {
                $monthlyRate = $availableMonthlyRates[0];
            } else {
                foreach ($availableMonthlyRates as $availableMonthlyRate) {
                    if ($monthlyRate === null || abs($context->getCalculationValue() - (float)$monthlyRate) > abs($availableMonthlyRate - $context->getCalculationValue())) {
                        $monthlyRate = $availableMonthlyRate;
                    } elseif ($availableMonthlyRate > $context->getCalculationValue()) {
                        // if it is not a match, and the calculated rate is already higher than the given value,
                        // we can cancel the loop, cause every higher values will not match, either.
                        break;
                    }
                }
            }
        } else {
            $monthlyRate = array_key_first($amountBuilders);
        }

        return new OfflineInstallmentCalculatorResult(
            $context,
            $amountBuilders[$monthlyRate][0],
            $amountBuilders[$monthlyRate][1],
            (float)$monthlyRate
        );
    }

    private function calculateMonthlyRate(?float $totalAmount, ProfileConfigMethodInstallmentEntity $config, $month)
    {
        $mbContent = new ModelBuilder('Content');
        $mbContent->setArray([
            'InstallmentCalculation' => [
                'Amount' => $totalAmount,
                'PaymentFirstday' => PaymentFirstday::getFirstdayForType($config->getDefaultPaymentType()),
                'InterestRate' => $config->getDefaultInterestRate(),
                'ServiceCharge' => $config->getServiceCharge(),
                'CalculationTime' => [
                    'Month' => $month
                ]
            ]
        ]);

        return (new OfflineInstallmentCalculation())->callOfflineCalculation($mbContent)->subtype('calculation-by-time');
    }

    public function getTranslations(SalesChannelContext $salesChannelContext): array
    {
        $langId = $salesChannelContext->getContext()->getLanguageId();
        if (!isset($this->_translationCache[$langId])) {
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

    /**
     * @return array{translations: array, plan: array, transactionId: string}
     * @throws ProfileNotFoundException
     * @throws RequestException
     * @throws TransactionIdFetchFailedException
     */
    public function getInstallmentPlanTwigVars(InstallmentCalculatorContext $context): array
    {
        $installmentPlan = $this->getInstallmentPlanData($context);

        $transactionId = $this->transactionIdService->getTransactionId(
            $context->getSalesChannelContext(),
            $context->getOrder() !== null ? TransactionIdService::PREFIX_ORDER . $context->getOrder()->getId() : TransactionIdService::PREFIX_CART,
            $installmentPlan['profileUuid']
        );

        return [
            'translations' => $this->getTranslations($context->getSalesChannelContext()),
            'plan' => $installmentPlan,
            'transactionId' => $transactionId,
        ];
    }
}
