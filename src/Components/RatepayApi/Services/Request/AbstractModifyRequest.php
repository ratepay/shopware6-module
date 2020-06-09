<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\FileLogger;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\HistoryLogger;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\RequestLogger;
use Ratepay\RatepayPayments\Core\PluginConfig\Services\ConfigService;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigRepository;
use RatePAY\RequestBuilder;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

abstract class AbstractModifyRequest extends AbstractOrderOperationRequest
{

    protected $_operation = self::CALL_CHANGE;
    /**
     * @var array
     */
    protected $items;

    /**
     * @var string
     */
    protected $eventName = null;

    /**
     * @var bool
     */
    protected $updateStock = false;

    /**
     * @var ShoppingBasketFactory
     */
    private $shoppingBasketFactory;
    /**
     * @var EntityRepositoryInterface
     */
    private $productRepository;
    /**
     * @var FileLogger
     */
    private $fileLogger;
    /**
     * @var EntityRepositoryInterface
     */
    private $lineItemsRepository;
    /**
     * @var HistoryLogger
     */
    private $historyLogger;
    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;

    public function __construct(
        ConfigService $configService,
        HeadFactory $headFactory,
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
        parent::__construct($configService, $requestLogger, $headFactory, $profileConfigRepository);
        $this->shoppingBasketFactory = $shoppingBasketFactory;
        $this->productRepository = $productRepository;
        $this->fileLogger = $fileLogger;
        $this->lineItemsRepository = $lineItemsRepository;
        $this->historyLogger = $historyLogger;
        $this->orderRepository = $orderRepository;
    }

    /**
     * key: product number
     * value: quantity
     * @param array $items
     */
    public final function setItems($items)
    {
        $this->items = $items;
    }

    protected function getRequestHead(ProfileConfigEntity $profileConfig): Head
    {
        $head = parent::getRequestHead($profileConfig);

        $head->setExternal($head->getExternal() ?: new Head\External());
        $head->getExternal()->setOrderId($this->order->getOrderNumber());
        $head->setTransactionId($this->order->getCustomFields()['ratepay_transaction_id']);
        return $head;
    }

    protected function getRequestContent(): Content
    {
        $content = new Content();
        $content->setShoppingBasket($this->shoppingBasketFactory->getData($this->order, $this->items));
        return $content;
    }

    protected function processSuccess(RequestBuilder $response)
    {
        $context = Context::createDefaultContext();
        $lineItems = $this->order->getLineItems()->getList(array_keys($this->items));
        $data = [];
        /** @var OrderLineItemEntity $item */
        foreach ($lineItems as $item) {
            $this->historyLogger->logHistory(
                $this->order->getId(),
                $this->eventName,
                $item->getLabel(),
                $item->getPayload()['productNumber'],
                $this->items[$item->getId()]
            );

            $newCustomFields = $this->getLineItemsCustomFieldChanges($item, $this->items[$item->getId()]);
            $data[] = [
                'id' => $item->getId(),
                'customFields' => array_replace($item->getCustomFields(), $newCustomFields)
            ];
        }
        if($data) {
            $this->lineItemsRepository->update($data, $context);
        }

        if(isset($this->items['shipping'])) {
            $orderCustomFields = $this->order->getCustomFields();
            $newCustomFields = $this->getShippingCustomFields($this->items['shipping']);
            $this->orderRepository->update([
                [
                    'id' => $this->order->getId(),
                    'customFields' => array_replace($orderCustomFields, $newCustomFields)
                ]
            ], $context);
        }

        if($this->updateStock) {
            $this->updateProductStocks();
        }
    }

    protected abstract function getLineItemsCustomFieldChanges(OrderLineItemEntity $lineItem, $qty);
    protected abstract function getShippingCustomFields($qty);

    protected function updateProductStocks()
    {
        $lineItems = $this->order->getLineItems()->getList(array_keys($this->items));
        $data = [];
        /** @var OrderLineItemEntity $item */
        foreach ($lineItems as $item) {
            $data[] = [
                'id' => $item->getProduct()->getId(),
                'stock' => $item->getProduct()->getStock() + $this->items[$item->getId()],
            ];
        }
        try {
            $this->productRepository->update($data, Context::createDefaultContext());
        } catch (\Exception $e) {
            $this->fileLogger->addError('Error during the updating of the stock (Exception: ' . $e->getMessage() . ')', [
                'orderId' => $this->order->getId(),
                'orderNumber' => $this->order->getOrderNumber(),
                'items' => $this->items
            ]);
        }
    }

    /**
     * @param bool $updateStock
     */
    public function setUpdateStock(bool $updateStock): void
    {
        $this->updateStock = $updateStock;
    }
}
