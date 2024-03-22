<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use RatePAY\Model\Request\SubModel\Head;
use RatePAY\Model\Request\SubModel\Head\Credential;
use RatePAY\Model\Request\SubModel\Head\Meta;
use RatePAY\Model\Request\SubModel\Head\Meta\Systems;
use RatePAY\Model\Request\SubModel\Head\Meta\Systems\System;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Core\Entity\Extension\OrderExtension;
use Ratepay\RpayPayments\Core\Entity\RatepayOrderDataEntity;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method Head getData(AbstractRequestData $requestData)
 */
class HeadFactory extends AbstractFactory
{
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        private readonly string $shopwareVersion,
        private readonly string $pluginVersion
    ) {
        parent::__construct($eventDispatcher);
    }

    protected function _getData(AbstractRequestData $requestData): ?object
    {
        $head = new Head();
        $head
            ->setSystemId($_SERVER['SERVER_ADDR'] ?? 'cli/cronjob/api')
            ->setMeta(
                (new Meta())
                    ->setSystems(
                        (new Systems())
                            ->setSystem(
                                (new System())
                                    ->setName('Shopware')
                                    ->setVersion($this->shopwareVersion . '_' . $this->pluginVersion)
                            )
                    )
            )
            ->setCredential(
                (new Credential())
                    ->setProfileId($requestData->getProfileConfig()->getProfileId())
                    ->setSecuritycode($requestData->getProfileConfig()->getSecurityCode())
            );

        if ($requestData instanceof OrderOperationData
            && !$requestData instanceof PaymentRequestData
            && $requestData->getTransaction()
        ) {
            /** @var RatepayOrderDataEntity $orderExtension */
            $orderExtension = $requestData->getOrder()->getExtension(OrderExtension::EXTENSION_NAME);
            $head->setTransactionId($orderExtension->getTransactionId());
        }

        return $head;
    }

    protected function isSupported(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof AbstractRequestData;
    }
}
