<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use RatePAY\Model\Request\SubModel\Head\Meta;
use RatePAY\Model\Request\SubModel\Head\Meta\Systems;
use RatePAY\Model\Request\SubModel\Head\Meta\Systems\System;
use RatePAY\Model\Request\SubModel\Head\Credential;
use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RpayPayments\Components\Checkout\Model\Extension\OrderExtension;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method getData(AbstractRequestData $requestData) : ?Head
 */
class HeadFactory extends AbstractFactory
{
    private string $shopwareVersion;

    private string $pluginVersion;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        string $shopwareVersion,
        string $pluginVersion
    ) {
        parent::__construct($eventDispatcher);
        $this->shopwareVersion = $shopwareVersion;
        $this->pluginVersion = $pluginVersion;
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

        if ($requestData instanceof PaymentRequestData && $requestData->getRatepayTransactionId()) {
            $head->setTransactionId($requestData->getRatepayTransactionId());
        } elseif ($requestData instanceof OrderOperationData && $requestData->getTransaction()) {
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
