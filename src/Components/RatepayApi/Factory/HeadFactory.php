<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Factory;


use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RatepayPayments\Components\PluginConfig\Service\ConfigService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class HeadFactory extends AbstractFactory
{

    private $shopwareVersion;
    /**
     * @var ConfigService
     */
    private $configService;

    public function __construct(EventDispatcherInterface $eventDispatcher, ConfigService $configService, $shopwareVersion)
    {
        parent::__construct($eventDispatcher);
        $this->configService = $configService;
        $this->shopwareVersion = $shopwareVersion;
    }

    public function _getData(IRequestData $requestData): ?object
    {
        $head = new Head();
        $head
            ->setSystemId(isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'cli/cronjob/api')
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
