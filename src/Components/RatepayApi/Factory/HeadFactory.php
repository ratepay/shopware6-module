<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\IRequestData;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class HeadFactory extends AbstractFactory
{
    private $shopwareVersion;

    /**
     * @var string
     */
    private $pluginVersion;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack,
        string $shopwareVersion,
        string $pluginVersion
    )
    {
        parent::__construct($eventDispatcher, $requestStack);
        $this->shopwareVersion = $shopwareVersion;
        $this->pluginVersion = $pluginVersion;
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
                                    ->setVersion($this->shopwareVersion . '/' . $this->pluginVersion)
                            )
                    )
            );

        return $head;
    }
}
