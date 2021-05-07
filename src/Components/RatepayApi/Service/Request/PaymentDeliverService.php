<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;

use RatePAY\Model\Request\SubModel\Content;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
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

    public const EVENT_BUILD_CONTENT = self::class . parent::EVENT_BUILD_CONTENT;

    public const EVENT_INIT_REQUEST = self::class . parent::EVENT_INIT_REQUEST;

    protected string $_operation = self::CALL_DELIVER;

    private InvoiceFactory $invoiceFactory;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        HeadFactory $headFactory,
        EntityRepositoryInterface $profileConfigRepository,
        ShoppingBasketFactory $shoppingBasketFactory,
        InvoiceFactory $invoiceFactory,
        ExternalFactory $externalFactory
    ) {
        parent::__construct($eventDispatcher, $headFactory, $profileConfigRepository, $shoppingBasketFactory, $externalFactory);
        $this->invoiceFactory = $invoiceFactory;
    }

    protected function getRequestContent(AbstractRequestData $requestData): ?Content
    {
        /** @var OrderOperationData $requestData */
        $content = parent::getRequestContent($requestData);
        if ($invoicing = $this->invoiceFactory->getData($requestData)) {
            $content->setInvoicing($invoicing);
        }

        return $content;
    }
}
