<?php


namespace Ratepay\RatepayPayments\Components\RatepayApi\Factory;


use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RatepayPayments\Core\PluginConfig\Services\ConfigService;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity;

class HeadFactory
{

    private $shopwareVersion;
    /**
     * @var ConfigService
     */
    private $configService;

    public function __construct(ConfigService $configService, $shopwareVersion)
    {
        $this->configService = $configService;
        $this->shopwareVersion = $shopwareVersion;
    }

    public function getData(ProfileConfigEntity $profileConfig)
    {
        $head = new Head();
        $head
            ->setSystemId(isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'cli/cronjob/api')
            ->setCredential(
                (new Head\Credential())
                    ->setProfileId($profileConfig->getProfileId())
                    ->setSecuritycode($profileConfig->getSecurityCode())
            )
            ->setMeta(
                (new Head\Meta())
                    ->setSystems(
                        (new Head\Meta\Systems())
                            ->setSystem(
                                (new Head\Meta\Systems\System())
                                    ->setName('Shopware')
                                    ->setVersion($this->shopwareVersion . '/' . $this->configService->getPluginVersion())
                            )
                    )

            );

        return $head;

    }


}
