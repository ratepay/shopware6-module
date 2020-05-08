<?php


namespace Ratepay\RatepayPayments\Core\ProfileConfig\Service;


use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigCollection;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigMethodEntity;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigMethodInstallmentEntity;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigRepository;
use Ratepay\RatepayPayments\Core\RatepayApi\Services\Request\ProfileRequestService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;

class ProfileConfigService
{

    const PAYMENT_METHOD_MAPPING = [
        ProfileConfigMethodEntity::PAYMENT_METHOD_PREPAYMENT => 'prepayment',
        ProfileConfigMethodEntity::PAYMENT_METHOD_DEBIT => 'elv',
        ProfileConfigMethodEntity::PAYMENT_METHOD_INVOICE => 'invoice',
        ProfileConfigMethodEntity::PAYMENT_METHOD_INSTALLMENT => 'installment',
        ProfileConfigMethodEntity::PAYMENT_METHOD_INSTALLMENT_0 => 'installment'
    ];

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

    public function __construct(
        EntityRepositoryInterface $repository,
        EntityRepositoryInterface $methodConfigRepository,
        EntityRepositoryInterface $methodConfigInstallmentRepository,
        ProfileRequestService $profileRequestService
    )
    {
        $this->repository = $repository;
        $this->methodConfigRepository = $methodConfigRepository;
        $this->methodConfigInstallmentRepository = $methodConfigInstallmentRepository;
        $this->profileRequestService = $profileRequestService;
    }

    public function refreshProfileConfigs(array $ids)
    {
        $context = Context::createDefaultContext();
        /** @var ProfileConfigCollection|ProfileConfigEntity[] $profileConfigs */
        $profileConfigs = $this->repository->search(new Criteria($ids), $context);

        foreach ($profileConfigs as $profileConfig) {
            // truncate payment configurations
            $entitiesToDelete = $this->methodConfigRepository->search(
                (new Criteria())->addFilter(new EqualsFilter(ProfileConfigMethodEntity::FIELD_PROFILE_ID, $profileConfig->getId())),
                $context
            );
            $deleteIds = $entitiesToDelete->getIds();
            if (count($deleteIds)) {
                $this->methodConfigRepository->delete(array_values(array_map(function ($id) {
                    return [
                        ProfileConfigMethodEntity::FIELD_ID => $id
                    ];
                }, $deleteIds)), $context);
            }

            $this->profileRequestService->setProfileConfig($profileConfig);
            $response = $this->profileRequestService->doRequest();
            $data = [
                ProfileConfigEntity::FIELD_ID => $profileConfig->getId()
            ];

            if ($response->isSuccessful() == false) {
                $data[ProfileConfigEntity::FIELD_STATUS] = false;
                $data[ProfileConfigEntity::FIELD_STATUS_MESSAGE] = $response->getReasonMessage();
            } else {
                $responseData = $response->getResult();
                $data[ProfileConfigEntity::FIELD_STATUS] = true;
                $data[ProfileConfigEntity::FIELD_COUNTRY_CODE_BILLING] = $responseData['merchantConfig']['country-code-billing'];
                $data[ProfileConfigEntity::FIELD_COUNTRY_CODE_SHIPPING] = $responseData['merchantConfig']['country-code-delivery'];
                $data[ProfileConfigEntity::FIELD_CURRENCY] = $responseData['merchantConfig']['currency'];


                $methodConfigs = [];
                $installmentConfigs = [];
                foreach (self::PAYMENT_METHOD_MAPPING as $method => $arrayKey) {
                    $id = Uuid::randomHex();
                    $methodConfigs[] = [
                        ProfileConfigMethodEntity::FIELD_ID => $id,
                        ProfileConfigMethodEntity::FIELD_PROFILE_ID => $profileConfig->getId(),
                        ProfileConfigMethodEntity::FIELD_PAYMENT_METHOD => $method,
                        ProfileConfigMethodEntity::FIELD_LIMIT_MIN => floatval($responseData['merchantConfig']['tx-limit-' . $arrayKey . '-min']),
                        ProfileConfigMethodEntity::FIELD_LIMIT_MAX => floatval($responseData['merchantConfig']['tx-limit-' . $arrayKey . '-max']),
                        ProfileConfigMethodEntity::FIELD_LIMIT_MAX_B2B => floatval($responseData['merchantConfig']['tx-limit-' . $arrayKey . '-max-b2b']),
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
            $this->repository->upsert([$data], $context);

            if (isset($methodConfigs)) {
                $this->methodConfigRepository->upsert($methodConfigs, $context);
            }
            if (isset($installmentConfigs)) {
                $this->methodConfigInstallmentRepository->upsert($installmentConfigs, $context);
            }
        }
        return $this->repository->search(new Criteria($ids), $context);
    }

}
