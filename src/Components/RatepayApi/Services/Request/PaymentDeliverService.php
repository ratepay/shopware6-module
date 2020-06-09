<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


use RatePAY\Model\Request\SubModel\Content;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\InvoiceFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\FileLogger;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\HistoryLogger;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\RequestLogger;
use Ratepay\RatepayPayments\Core\PluginConfig\Services\ConfigService;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigRepository;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class PaymentDeliverService extends AbstractModifyRequest
{

    protected $_operation = self::CALL_DELIVER;
    protected $eventName = 'deliver';

    /**
     * @var InvoiceFactory
     */
    private $invoiceFactory;

    public function __construct(
        ConfigService $configService,
        HeadFactory $headFactory,
        InvoiceFactory $invoiceFactory,
        ShoppingBasketFactory $shoppingBasketFactory,
        ProfileConfigRepository $profileConfigRepository,
        EntityRepositoryInterface $productRepository,
        EntityRepositoryInterface $orderRepository,
        EntityRepositoryInterface $lineItemsRepository,
        RequestLogger $requestLogger,
        FileLogger $fileLogger,
        HistoryLogger $historyLogger
    )
    {
        parent::__construct($configService, $headFactory, $shoppingBasketFactory, $profileConfigRepository, $productRepository, $orderRepository, $lineItemsRepository, $requestLogger, $fileLogger, $historyLogger);
        $this->invoiceFactory = $invoiceFactory;
    }

    protected function getRequestContent(): Content
    {
        $content = parent::getRequestContent();
        if ($invoicing = $this->invoiceFactory->getData($this->order)) {
            $content->setInvoicing($invoicing);
        }
        return $content;
    }

    protected function getLineItemsCustomFieldChanges(OrderLineItemEntity $lineItem, $qty)
    {
        return [
            'ratepay_delivered' => $lineItem->getCustomFields()['ratepay_delivered'] + $qty
        ];
    }

    protected function getShippingCustomFields($qty)
    {
        return [
            'ratepay_shipping_delivered' => $qty
        ];
    }
}
