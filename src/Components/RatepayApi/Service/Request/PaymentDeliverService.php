<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Service\Request;


use RatePAY\Model\Request\SubModel\Content;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\InvoiceFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RatepayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RatepayPayments\Components\ProfileConfig\Model\Repository\ProfileConfigRepository;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PaymentDeliverService extends AbstractModifyRequest
{

    const EVENT_SUCCESSFUL = self::class . parent::EVENT_SUCCESSFUL;
    const EVENT_FAILED = self::class . parent::EVENT_FAILED;
    const EVENT_BUILD_HEAD = self::class . parent::EVENT_BUILD_HEAD;

    protected $_operation = self::CALL_DELIVER;

    /**
     * @var InvoiceFactory
     */
    private $invoiceFactory;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ConfigService $configService,
        HeadFactory $headFactory,
        ProfileConfigRepository $profileConfigRepository,
        ShoppingBasketFactory $shoppingBasketFactory,
        InvoiceFactory $invoiceFactory
    )
    {
        parent::__construct($eventDispatcher, $configService, $headFactory, $profileConfigRepository, $shoppingBasketFactory);
        $this->invoiceFactory = $invoiceFactory;
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

}
