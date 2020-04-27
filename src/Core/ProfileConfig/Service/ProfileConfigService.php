<?php


namespace RatePay\RatePayPayments\Core\ProfileConfig\Service;


use RatePay\RatePayPayments\Core\ProfileConfig\ProfileConfigCollection;
use RatePay\RatePayPayments\Core\RatePayApi\Services\Request\ProfileRequestService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

class ProfileConfigService
{

    /**
     * @var EntityRepositoryInterface
     */
    private $repository;
    /**
     * @var ProfileRequestService
     */
    private $profileRequestService;

    public function __construct(EntityRepositoryInterface $repository, ProfileRequestService $profileRequestService)
    {
        $this->repository = $repository;
        $this->profileRequestService = $profileRequestService;
    }

    public function refreshProfileConfigs(array $ids) {
        /** @var ProfileConfigCollection $profileConfigs */
        $profileConfigs = $this->repository->search(new Criteria($ids), Context::createDefaultContext());

        $updates = [];
        foreach($profileConfigs as $profileConfig) {
            $this->profileRequestService->setProfileConfig($profileConfig);
            $response = $this->profileRequestService->doRequest();
            if($response->isSuccessful() == false) {
                $profileConfig->setStatus(false);
                $profileConfig->setStatusMessage($response->getReasonMessage());
            }
            print_r($response);
            echo "test";die;
            print_r($response);die;
            //$updates[] = $profileConfig->getVars();
        }
        print_r($updates);die;
    }

}
