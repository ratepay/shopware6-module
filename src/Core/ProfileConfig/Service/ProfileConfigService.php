<?php


namespace Ratepay\RatepayPayments\Core\ProfileConfig\Service;


use RatePAY\Model\Request\AbstractRequest;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentPaymentHandler;
use Ratepay\RatepayPayments\Components\PaymentHandler\InstallmentZeroPercentPaymentHandler;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigCollection;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigMethodEntity;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigMethodInstallmentEntity;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigRepository;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\Request\ProfileRequestService;
use Ratepay\RatepayPayments\RatepayPayments;
use Shopware\Core\Checkout\Payment\PaymentMethodCollection;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class ProfileConfigService
{

    /**
     * @var ProfileConfigRepository
     */
    private $repository;
    /**
     * @var ProfileRequestService
     */
    private $profileRequestService;
    /**
     * @var EntityRepositoryInterface
     */
    private $methodConfigRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $methodConfigInstallmentRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $paymentRepository;
    /**
     * @var Context
     */
    protected $context;

    public function __construct(
        EntityRepositoryInterface $paymentRepository,
        EntityRepositoryInterface $repository,
        EntityRepositoryInterface $methodConfigRepository,
        EntityRepositoryInterface $methodConfigInstallmentRepository,
        ProfileRequestService $profileRequestService
    )
    {
        $this->paymentRepository = $paymentRepository;
        $this->repository = $repository;
        $this->methodConfigRepository = $methodConfigRepository;
        $this->methodConfigInstallmentRepository = $methodConfigInstallmentRepository;
        $this->profileRequestService = $profileRequestService;

        $this->context = Context::createDefaultContext();
    }

    public function refreshProfileConfigs(array $ids)
    {

        /** @var ProfileConfigCollection|ProfileConfigEntity[] $profileConfigs */
        $profileConfigs = $this->repository->search(new Criteria($ids), $this->context);

        foreach ($profileConfigs as $profileConfig) {
            // truncate payment configurations
            $entitiesToDelete = $this->methodConfigRepository->search(
                (new Criteria())->addFilter(new EqualsFilter(ProfileConfigMethodEntity::FIELD_PROFILE_ID, $profileConfig->getId())),
                $this->context
            );
            $deleteIds = $entitiesToDelete->getIds();
            if (count($deleteIds)) {
                $this->methodConfigRepository->delete(array_values(array_map(function ($id) {
                    return [
                        ProfileConfigMethodEntity::FIELD_ID => $id
                    ];
                }, $deleteIds)), $this->context);
            }

            $this->profileRequestService->setProfileConfig($profileConfig);
            $response = $this->profileRequestService->doRequest();
            $data = [
                ProfileConfigEntity::FIELD_ID => $profileConfig->getId()
            ];

            $responseData = $response->getResult();
            if ($response->isSuccessful() == false) {
                $data[ProfileConfigEntity::FIELD_STATUS] = false;
                $data[ProfileConfigEntity::FIELD_STATUS_MESSAGE] = $response->getReasonMessage();
            } else if($responseData['merchantConfig']['merchant-status'] == 1) {
                $data[ProfileConfigEntity::FIELD_STATUS] = false;
                $data[ProfileConfigEntity::FIELD_STATUS_MESSAGE] = 'The profile is disabled. Please contact your account manager.';
            } else {
                $data[ProfileConfigEntity::FIELD_STATUS] = true;
                $data[ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING] = $responseData['merchantConfig']['country-code-billing'];
                $data[ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING] = $responseData['merchantConfig']['country-code-delivery'];
                $data[ProfileConfigEntity::FIELD_CURRENCY] = $responseData['merchantConfig']['currency'];


                $methodConfigs = [];
                $installmentConfigs = [];


                $paymentMethods = $this->getPaymentMethods();

                /** @var PaymentMethodEntity $paymentMethod */
                foreach ($paymentMethods as $paymentMethod) {
                    $arrayKey = strtolower(constant($paymentMethod->getHandlerIdentifier().'::RATEPAY_METHOD'));
                    if($responseData['merchantConfig']['activation-status-'.$arrayKey] == 1) {
                        // method is disabled.
                        continue;
                    }
                    if($paymentMethod->getHandlerIdentifier() === InstallmentZeroPercentPaymentHandler::class  && $responseData['installmentConfig']['interestrate-min'] > 0) {
                        // this is not a zero percent installment profile.
                        continue;
                    }
                    if($paymentMethod->getHandlerIdentifier() === InstallmentPaymentHandler::class && $responseData['installmentConfig']['interestrate-min'] == 0) {
                        // this is a zero percent installment profile, not a standard installment.
                        continue;
                    }
                    $id = Uuid::randomHex();
                    $methodConfigs[] = [
                        ProfileConfigMethodEntity::FIELD_ID => $id,
                        ProfileConfigMethodEntity::FIELD_PROFILE_ID => $profileConfig->getId(),
                        ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD_ID => $paymentMethod->getId(),
                        ProfileConfigMethodEntity::FIELD_LIMIT_MIN => floatval($responseData['merchantConfig']['tx-limit-' . $arrayKey . '-min']) ? : null,
                        ProfileConfigMethodEntity::FIELD_LIMIT_MAX => floatval($responseData['merchantConfig']['tx-limit-' . $arrayKey . '-max']) ? : null,
                        ProfileConfigMethodEntity::FIELD_LIMIT_MAX_B2B => floatval($responseData['merchantConfig']['tx-limit-' . $arrayKey . '-max-b2b']) ? : null,
                        ProfileConfigMethodEntity::FIELD_ALLOW_B2B => $responseData['merchantConfig']['b2b-' . $arrayKey] == 'yes',
                        ProfileConfigMethodEntity::FIELD_ALLOW_DIFFERENT_ADDRESSES => $responseData['merchantConfig']['delivery-address-' . $arrayKey] == 'yes',
                    ];
                    if ($arrayKey === 'installment') {
                        $paymentFirstDay = explode(',', $responseData['installmentConfig']['valid-payment-firstdays']);
                        $installmentConfigs[] = [
                            ProfileConfigMethodInstallmentEntity::FIELD_ID => $id,
                            ProfileConfigMethodInstallmentEntity::FIELD_ALLOWED_MONTHS => array_map('intval', explode(',', $responseData['installmentConfig']['month-allowed'])),
                            ProfileConfigMethodInstallmentEntity::FIELD_IS_BANKTRANSFER_ALLOWED => in_array(28, $paymentFirstDay),
                            ProfileConfigMethodInstallmentEntity::FIELD_IS_DEBIT_ALLOWED => in_array(2, $paymentFirstDay),
                            ProfileConfigMethodInstallmentEntity::FIELD_RATE_MIN => floatval($responseData['installmentConfig']['rate-min-normal']),
                        ];
                    }
                }
            }
            $this->repository->upsert([$data], $this->context);

            if (isset($methodConfigs) && count($methodConfigs)) {
                $this->methodConfigRepository->upsert($methodConfigs, $this->context);
            }
            if (isset($installmentConfigs) && count($installmentConfigs)) {
                $this->methodConfigInstallmentRepository->upsert($installmentConfigs, $this->context);
            }
        }
        return $this->repository->search(new Criteria($ids), $this->context);
    }

    /**
     * @returns PaymentMethodCollection
     */
    private function getPaymentMethods()
    {
        $criteria = new Criteria();
        $criteria->addAssociation('plugin');
        $criteria->addFilter(new EqualsFilter('plugin.baseClass', RatepayPayments::class));
        return $this->paymentRepository->search($criteria, $this->context);
    }

}
