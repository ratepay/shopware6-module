<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\ProfileConfig\Service;

use RatePAY\Model\Response\ProfileRequest;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\Collection\ProfileConfigCollection;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigMethodEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\ProfileRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Service\Request\ProfileRequestService;
use Ratepay\RpayPayments\Util\CriteriaHelper;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ProfileConfigManagement
{
    private Context $context;

    private EntityRepository $repository;

    private ProfileRequestService $profileRequestService;

    private EntityRepository $methodConfigRepository;

    private EntityRepository $methodConfigInstallmentRepository;

    private ProfileConfigResponseConverter $profileConfigResponseConverter;

    public function __construct(
        EntityRepository $repository,
        EntityRepository $methodConfigRepository,
        EntityRepository $methodConfigInstallmentRepository,
        ProfileRequestService $profileRequestService,
        ProfileConfigResponseConverter $profileConfigResponseConverter
    )
    {
        $this->repository = $repository;
        $this->methodConfigRepository = $methodConfigRepository;
        $this->methodConfigInstallmentRepository = $methodConfigInstallmentRepository;
        $this->profileRequestService = $profileRequestService;
        $this->profileConfigResponseConverter = $profileConfigResponseConverter;

        $this->context = Context::createDefaultContext();
    }

    public function refreshProfileConfigs(array $ids): EntitySearchResult
    {
        /** @var ProfileConfigCollection|ProfileConfigEntity[] $profileConfigs */
        $profileConfigs = $this->repository->search(new Criteria($ids), $this->context);

        foreach ($profileConfigs as $profileConfig) {
            $this->deleteMethodConfigsForProfile($profileConfig);

            /** @var ProfileRequest $response */
            $response = $this->profileRequestService->doRequest(
                new ProfileRequestData($this->context, $profileConfig)
            )->getResponse();

            [$profileConfigData, $methodConfigs, $installmentConfigs] = $this->profileConfigResponseConverter->convert(
                $response,
                $profileConfig->getId()
            );
            $profileConfigData[ProfileConfigEntity::FIELD_ID] = $profileConfig->getId();

            $this->repository->upsert([$profileConfigData], $this->context);

            if (isset($methodConfigs) && (is_countable($methodConfigs) ? count($methodConfigs) : 0)) {
                $this->methodConfigRepository->upsert($methodConfigs, $this->context);
            }

            if (isset($installmentConfigs) && (is_countable($installmentConfigs) ? count($installmentConfigs) : 0)) {
                $this->methodConfigInstallmentRepository->upsert($installmentConfigs, $this->context);
            }
        }

        return $this->repository->search(CriteriaHelper::getCriteriaForProfileConfig($ids), $this->context);
    }

    protected function deleteMethodConfigsForProfile(ProfileConfigEntity $profileConfig): void
    {
        $entitiesToDelete = $this->methodConfigRepository->search(
            (new Criteria())->addFilter(new EqualsFilter(ProfileConfigMethodEntity::FIELD_PROFILE_ID, $profileConfig->getId())),
            $this->context
        );
        $deleteIds = $entitiesToDelete->getIds();
        if ($deleteIds !== []) {
            $this->methodConfigRepository->delete(array_values(array_map(static fn($id): array => [
                ProfileConfigMethodEntity::FIELD_ID => $id,
            ], $deleteIds)), $this->context);
        }
    }
}
