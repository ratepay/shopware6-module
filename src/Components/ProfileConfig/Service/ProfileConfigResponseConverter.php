<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Service;

use RatePAY\Model\Response\ProfileRequest;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RpayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodInstallmentEntity;
use Ratepay\RpayPayments\RpayPayments;
use Ratepay\RpayPayments\Util\PaymentFirstday;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class ProfileConfigResponseConverter
{
    /**
     * @var EntityRepository
     * the interface has been deprecated, but shopware is using the Interface in a decorator for the repository.
     * so it will crash, if we are only using EntityRepository, cause an object of the decorator got injected into the constructor.
     * After Shopware has removed the decorator, we can replace this by a normal definition
     * TODO remove comment on Shopware Version 6.5.0.0 & readd type hint & change constructor argument type
     */
    private object $paymentMethodRepository;

    /**
     * @var PaymentMethodEntity[]|null
     */
    private ?array $paymentMethods = null;

    public function __construct(object $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    /**
     * converts the response of the ProfileRequest to the data which will be submitted to the database.
     */
    public function convert(ProfileRequest $response, string $profileId): array
    {
        $profileConfigData = [];
        $responseData = $response->getResult(false);

        if (!$response->isSuccessful()) {
            $profileConfigData[ProfileConfigEntity::FIELD_STATUS] = false;
            $profileConfigData[ProfileConfigEntity::FIELD_STATUS_MESSAGE] = $response->getReasonMessage();
        } elseif (((int) $responseData['merchantConfig']['merchant-status']) === 1) {
            $profileConfigData[ProfileConfigEntity::FIELD_STATUS] = false;
            $profileConfigData[ProfileConfigEntity::FIELD_STATUS_MESSAGE] = 'The profile is disabled. Please contact your account manager.';
        } else {
            $profileConfigData[ProfileConfigEntity::FIELD_STATUS] = true;
            $profileConfigData[ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING] = explode(',', $responseData['merchantConfig']['country-code-billing']);
            $profileConfigData[ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING] = explode(',', $responseData['merchantConfig']['country-code-delivery']);
            $profileConfigData[ProfileConfigEntity::FIELD_CURRENCY] = explode(',', $responseData['merchantConfig']['currency']);

            $methodConfigs = [];
            $installmentConfigs = [];

            $paymentMethods = $this->getPaymentMethods();

            /** @var PaymentMethodEntity $paymentMethod */
            foreach ($paymentMethods as $paymentMethod) {
                $arrayKey = strtolower(constant($paymentMethod->getHandlerIdentifier() . '::RATEPAY_METHOD'));

                if (!isset($responseData['merchantConfig']['activation-status-' . $arrayKey]) ||
                    (((int) $responseData['merchantConfig']['activation-status-' . $arrayKey]) === 1)) {
                    // method is disabled.
                    continue;
                }

                if (isset($responseData['installmentConfig'])) {
                    if (((int) $responseData['installmentConfig']['interestrate-min']) > 0 &&
                        $paymentMethod->getHandlerIdentifier() === InstallmentZeroPercentPaymentHandler::class
                    ) {
                        // this is not a zero percent installment profile.
                        continue;
                    }

                    if (((int) $responseData['installmentConfig']['interestrate-min']) === 0 &&
                        $paymentMethod->getHandlerIdentifier() === InstallmentPaymentHandler::class
                    ) {
                        // this is a zero percent installment profile, not a standard installment.
                        continue;
                    }
                }

                $id = Uuid::randomHex();
                $methodConfigs[] = [
                    ProfileConfigMethodEntity::FIELD_ID => $id,
                    ProfileConfigMethodEntity::FIELD_PROFILE_ID => $profileId,
                    ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD_ID => $paymentMethod->getId(),
                    ProfileConfigMethodEntity::FIELD_LIMIT_MIN => ((float) $responseData['merchantConfig']['tx-limit-' . $arrayKey . '-min']) ?: null,
                    ProfileConfigMethodEntity::FIELD_LIMIT_MAX => ((float) $responseData['merchantConfig']['tx-limit-' . $arrayKey . '-max']) ?: null,
                    ProfileConfigMethodEntity::FIELD_LIMIT_MAX_B2B => ((float) $responseData['merchantConfig']['tx-limit-' . $arrayKey . '-max-b2b']) ?: null,
                    ProfileConfigMethodEntity::FIELD_ALLOW_B2B => $responseData['merchantConfig']['b2b-' . $arrayKey] === 'yes',
                    ProfileConfigMethodEntity::FIELD_ALLOW_DIFFERENT_ADDRESSES => $responseData['merchantConfig']['delivery-address-' . $arrayKey] === 'yes',
                ];
                if ($arrayKey === 'installment') {
                    $paymentFirstDay = explode(',', $responseData['installmentConfig']['valid-payment-firstdays']);
                    $installmentConfigs[] = [
                        ProfileConfigMethodInstallmentEntity::FIELD_ID => $id,
                        ProfileConfigMethodInstallmentEntity::FIELD_ALLOWED_MONTHS => array_map('intval', explode(',', $responseData['installmentConfig']['month-allowed'])),
                        ProfileConfigMethodInstallmentEntity::FIELD_IS_BANKTRANSFER_ALLOWED => in_array(PaymentFirstday::BANK_TRANSFER, $paymentFirstDay, false),
                        ProfileConfigMethodInstallmentEntity::FIELD_IS_DEBIT_ALLOWED => in_array(PaymentFirstday::DIRECT_DEBIT, $paymentFirstDay, false),
                        ProfileConfigMethodInstallmentEntity::FIELD_RATE_MIN => (float) $responseData['installmentConfig']['rate-min-normal'],
                        ProfileConfigMethodInstallmentEntity::FIELD_DEFAULT_PAYMENT_TYPE => (int)$responseData['installmentConfig']['payment-firstday'] === 2 ? ProfileConfigMethodInstallmentEntity::PAYMENT_TYPE_DIRECT_DEBIT : ProfileConfigMethodInstallmentEntity::PAYMENT_TYPE_BANK_TRANSFER,
                        ProfileConfigMethodInstallmentEntity::FIELD_DEFAULT_INTEREST_RATE => (float) $responseData['installmentConfig']['interestrate-default'],
                        ProfileConfigMethodInstallmentEntity::FIELD_SERVICE_CHARGE => (float) $responseData['installmentConfig']['service-charge'],

                    ];
                }
            }

            return [$profileConfigData, $methodConfigs, $installmentConfigs];
        }

        return [$profileConfigData, [], []];
    }

    /**
     * @return PaymentMethodEntity[]
     */
    private function getPaymentMethods(): array
    {
        if ($this->paymentMethods === null) {
            $criteria = new Criteria();
            $criteria->addAssociation('plugin');
            $criteria->addFilter(new EqualsFilter('plugin.baseClass', RpayPayments::class));
            $this->paymentMethods = $this->paymentMethodRepository->search($criteria, Context::createDefaultContext())->getElements();
        }

        return $this->paymentMethods;
    }
}
