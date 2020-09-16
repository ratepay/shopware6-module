<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;

use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RpayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ExternalFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\InvoiceFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PaymentDeliverService extends AbstractModifyRequest
{
    public const EVENT_SUCCESSFUL = self::class . parent::EVENT_SUCCESSFUL;

    public const EVENT_FAILED = self::class . parent::EVENT_FAILED;

    public const EVENT_BUILD_HEAD = self::class . parent::EVENT_BUILD_HEAD;

    protected $_operation = self::CALL_DELIVER;

    /**
     * @var InvoiceFactory
     */
    private $invoiceFactory;

    /**
     * @var ExternalFactory
     */
    private $externalFactory;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ConfigService $configService,
        HeadFactory $headFactory,
        EntityRepositoryInterface $profileConfigRepository,
        ShoppingBasketFactory $shoppingBasketFactory,
        InvoiceFactory $invoiceFactory,
        ExternalFactory $externalFactory
    ) {
        parent::__construct($eventDispatcher, $configService, $headFactory, $profileConfigRepository, $shoppingBasketFactory);
        $this->invoiceFactory = $invoiceFactory;
        $this->externalFactory = $externalFactory;
    }

    protected function getRequestContent(IRequestData $requestData): ?Content
    {
        /** @var OrderOperationData $requestData */
        $content = parent::getRequestContent($requestData);
        if ($invoicing = $this->invoiceFactory->getData($requestData)) {
            $content->setInvoicing($invoicing);
        }

        return $content;
    }

    protected function getRequestHead(IRequestData $requestData, ProfileConfigEntity $profileConfig): Head
    {
        $head = parent::getRequestHead($requestData, $profileConfig);
        $data = $this->externalFactory->getData($requestData);
        if ($data) {
            $head->setExternal($data);
        }

        return $head;
    }
}
