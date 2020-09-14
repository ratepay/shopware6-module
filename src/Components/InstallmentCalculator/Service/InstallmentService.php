<?php
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\InstallmentCalculator\Service;

use RatePAY\Frontend\InstallmentBuilder;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Util\PlanHasher;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\ProfileConfigService;
use RatePAY\Service\LanguageService;
use RuntimeException;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class InstallmentService
{

    /**
     * @var ProfileConfigService
     */
    protected $profileConfigService;
    /**
     * @var CartService
     */
    private $cartService;
    /**
     * @var EntityRepositoryInterface
     */
    private $languageRepository;

    private $_translationCache;

    public function __construct(
        CartService $cartService,
        EntityRepositoryInterface $languageRepository,
        ProfileConfigService $profileConfigService
    )
    {
        $this->cartService = $cartService;
        $this->languageRepository = $languageRepository;
        $this->profileConfigService = $profileConfigService;
    }

    public function getInstallmentPlanData(SalesChannelContext $salesChannelContext, $type, $value, $cartTotalAmount = null)
    {
        $installmentBuilder = $this->getInstallmentBuilder($salesChannelContext);
        if ($cartTotalAmount === null) {
            $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);
            $cartTotalAmount = $cart->getPrice()->getTotalPrice();
        }
        $json = $installmentBuilder->getInstallmentPlanAsJson($cartTotalAmount, $type, $value);
        $data = json_decode($json, true);
        $data['hash'] = PlanHasher::hashPlan($data);
        return $data;
    }

    protected function getInstallmentBuilder(SalesChannelContext $context): InstallmentBuilder
    {
        $profileConfig = $this->profileConfigService->getProfileConfigBySalesChannel($context);

        if ($profileConfig === null) {
            throw new RuntimeException('no profile id found');
        }

        $installmentBuilder = new InstallmentBuilder(
            $profileConfig->isSandbox(),
            $profileConfig->getProfileId(),
            $profileConfig->getSecurityCode(),
            $context->getContext()->getLanguageId(),
            $context->getCustomer()->getActiveBillingAddress()->getCountry()->getIso()
        );
        return $installmentBuilder;
    }

    public function getInstallmentCalculatorData(SalesChannelContext $salesChannelContext): array
    {
        $installmentBuilder = $this->getInstallmentBuilder($salesChannelContext);
        $cart = $this->cartService->getCart($salesChannelContext->getToken(), $salesChannelContext);

        /** @var LanguageEntity $language */
        $json = $installmentBuilder->getInstallmentCalculatorAsJson($cart->getPrice()->getTotalPrice());
        $data = json_decode($json, true);
        $data['defaults']['type'] = 'time';
        $data['defaults']['value'] = $data['rp_allowedMonths'][0];
        $data['defaults']['paymentType'] = $data['rp_debitPayType']['rp_paymentType_directDebit'] ? 'DIRECT-DEBIT' : 'BANK-TRANSFER';
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

}
