<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


use Enlight_Components_Db_Adapter_Pdo_Mysql;
use RpayRatepay\Component\Mapper\BasketArrayBuilder;
use RpayRatepay\Helper\PositionHelper;
use RpayRatepay\Services\Config\ConfigService;
use RpayRatepay\Services\ProfileConfig\ProfileConfigService;
use RpayRatepay\Services\Factory\InvoiceArrayFactory;
use RpayRatepay\Services\Logger\HistoryLogger;
use RpayRatepay\Services\Logger\RequestLogger;
use Shopware\Components\Model\ModelManager;

class PaymentDeliverService extends AbstractModifyRequest
{

    /**
     * @var InvoiceArrayFactory
     */
    protected $invoiceArrayFactory;

    public function __construct(
        Enlight_Components_Db_Adapter_Pdo_Mysql $db,
        ConfigService $configService,
        RequestLogger $requestLogger,
        ProfileConfigService $profileConfigService,
        HistoryLogger $historyLogger,
        ModelManager $modelManager,
        PositionHelper $positionHelper,
        InvoiceArrayFactory $invoiceArrayFactory
    )
    {
        parent::__construct($db, $configService, $requestLogger, $profileConfigService, $historyLogger, $modelManager, $positionHelper);
        $this->invoiceArrayFactory = $invoiceArrayFactory;
    }

    protected function getCallName()
    {
        return self::CALL_DELIVER;
    }

    protected function isSkipRequest()
    {
        /** @deprecated v6.2 */
        if ($this->_order->getAttribute()->getRatepayDirectDelivery() == false) {
            if ($this->positionHelper->doesOrderHasOpenPositions($this->_order, $this->items)) {
                return true;
            } else {
                //deliver ALL positions!
                $basketArrayBuilder = new BasketArrayBuilder($this->_order, $this->_order->getDetails());
                $basketArrayBuilder->addShippingItem();
                $this->setItems($basketArrayBuilder);
                return false;
            }
        }
        return false;
    }

    protected function getRequestContent()
    {
        $requestContent = parent::getRequestContent();

        $invoiceData = $this->invoiceArrayFactory->getData($this->_order);
        if ($invoiceData) {
            $requestContent[InvoiceArrayFactory::ARRAY_KEY] = $invoiceData;
        }
        return $requestContent;
    }

    protected function processSuccess()
    {
        foreach ($this->items as $basketPosition) {
            $position = $this->getOrderPosition($basketPosition);

            if ($this->_order->getAttribute()->getRatepayDirectDelivery() == false && $this->isRequestSkipped == false) {
                /** @deprecated v6.2 */
                // this is a little bit tricky:
                // if a rate payment has been processed and all items has been delivered,
                // we must set the delivered value to the final value manually
                // do not decrease with the returned items, cause the returned items should be always zero
                $position->setDelivered($position->getOrderedQuantity() - $position->getCancelled());
            } else {
                $position->setDelivered($position->getDelivered() + $basketPosition->getQuantity());
            }

            $this->modelManager->flush($position);

            if ($this->isRequestSkipped === false) {
                $this->historyLogger->logHistory($position, $basketPosition->getQuantity(), 'Artikel wurde versand.');
            } else {
                $this->historyLogger->logHistory($position, $basketPosition->getQuantity(), 'Artikel wurde für den Versand vorbereitet.');
            }
        }
        parent::processSuccess();
    }

}
