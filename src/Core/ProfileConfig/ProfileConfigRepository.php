<?php


namespace Ratepay\RatepayPayments\Core\ProfileConfig;


use Ratepay\RatepayPayments\Core\ProfileConfig\Service\ProfileConfigService;
use Ratepay\RatepayPayments\Core\RatepayApi\Services\Request\ProfileRequestService;
use Shopware\Core\Framework\Api\Exception\ResourceNotFoundException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Event\NestedEventCollection;
use Symfony\Component\Messenger\Exception\ValidationFailedException;

class ProfileConfigRepository implements EntityRepositoryInterface
{

    /**
     * @var EntityRepositoryInterface
     */
    private $innerRepo;
    /**
     * @var ProfileRequestService
     */
    private $profileRequestService;

    public function __construct(EntityRepositoryInterface $innerRepo, ProfileRequestService $profileRequestService)
    {
        $this->innerRepo = $innerRepo;
        $this->profileRequestService = $profileRequestService;
    }

    public function update(array $data, Context $context): EntityWrittenContainerEvent
    {
        foreach($data as &$singleData) {

            $elements = $this->search(new Criteria([$singleData['id']]), $context);
            /** @var ProfileConfigEntity $profileConfig */
            $profileConfig = $elements->first();

            if(isset($singleData['profileId'])) {
                $profileConfig->setProfileId($singleData['profileId']);
            }
            if(isset($singleData['securityCode'])) {
                $profileConfig->setSecurityCode($singleData['securityCode']);
            }
            if(isset($singleData['sandbox'])) {
                $profileConfig->setSandbox($singleData['sandbox'] == 1);
            }

            $this->profileRequestService->setProfileConfig($profileConfig);
            $response = $this->profileRequestService->doRequest();
            if ($response->isSuccessful() == false) {
                $singleData['status'] = false;
                $singleData['statusMessage'] = $response->getResultMessage().': '.$response->getReasonMessage();
            } else {
                $responseResult = $response->getResult();
                $singleData['status'] = $responseResult['merchantConfig']['merchant-status'] == 2;
                $singleData['statusMessage'] = $response->getResultMessage().': '.$response->getReasonMessage() . ($singleData['status'] === false ? ' (Profile is disabled by RatePAY)' : '');
                //$singleData['profileId'] = $responseResult['merchantConfig']['profile-id'];
                $singleData['countryCodeBilling'] = strtoupper($responseResult['merchantConfig']['country-code-delivery']);
                $singleData['countryCodeDelivery'] = strtoupper($responseResult['merchantConfig']['country-code-delivery']);
                $singleData['currency'] = strtoupper($responseResult['merchantConfig']['currency']);
            }
        }

        return $this->innerRepo->update($data, $context);
    }

    public function upsert(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->innerRepo->upsert($data, $context);
    }

    public function create(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->innerRepo->create($data, $context);
    }


    // Unchanged methods

    public function aggregate(Criteria $criteria, Context $context): AggregationResultCollection
    {
        return $this->innerRepo->aggregate($criteria, $context);
    }

    public function delete(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->innerRepo->delete($data, $context);
    }

    public function searchIds(Criteria $criteria, Context $context): IdSearchResult
    {
        return $this->innerRepo->searchIds($criteria, $context);
    }

    public function clone(string $id, Context $context, ?string $newId = null): EntityWrittenContainerEvent
    {
        return $this->innerRepo->clone($id, $context, $newId);
    }

    public function search(Criteria $criteria, Context $context): EntitySearchResult
    {
        return $this->innerRepo->search($criteria, $context);
    }

    public function createVersion(string $id, Context $context, ?string $name = null, ?string $versionId = null): string
    {
        return $this->innerRepo->createVersion($id, $context, $name, $versionId);
    }

    public function merge(string $versionId, Context $context): void
    {
        $this->innerRepo->merge($versionId, $context);
    }

    public function getDefinition(): EntityDefinition
    {
        return $this->innerRepo->getDefinition();
    }

}
