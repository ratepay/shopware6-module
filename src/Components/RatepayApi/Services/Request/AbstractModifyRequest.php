<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


use Exception;
use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use Ratepay\RatepayPayments\Components\OrderManagement\Util\LineItemUtil;
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
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;

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
    protected $productRepository;
    /**
     * @var FileLogger
     */
    private $fileLogger;
    /**
     * @var EntityRepositoryInterface
     */
    protected $lineItemsRepository;
    /**
     * @var HistoryLogger
     */
    private $historyLogger;
    /**
     * @var EntityRepositoryInterface
     */
    protected $orderRepository;

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
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    protected function getRequestHead(ProfileConfigEntity $profileConfig): Head
    {
        $head = parent::getRequestHead($profileConfig);

        $head->setExternal($head->getExternal() ?: new Head\External());
        $head->getExternal()->setOrderId($this->order->getOrderNumber());
        $head->setTransactionId($this->order->getCustomFields()['ratepay']['transaction_id']);
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
        $lineItems = $this->lineItemsRepository->search(new Criteria(array_keys($this->items)), $this->context);
        $data = [];
        /** @var OrderLineItemEntity $item */
        foreach ($lineItems as $item) {

            $this->historyLogger->logHistory(
                $this->order->getId(),
                $this->eventName,
                $item->getLabel(),
                $item->getPayload()['productNumber'] ?? '',
                $this->items[$item->getId()]
            );

            $customFields = $item->getCustomFields();
            $ratepayCustomFields = $item->getCustomFields()['ratepay'] ?? LineItemUtil::getEmptyCustomFields();
            $this->updateCustomField($ratepayCustomFields, $this->items[$item->getId()]);
            $customFields['ratepay'] = $ratepayCustomFields;
            $data[] = [
                'id' => $item->getId(),
                'customFields' => $customFields
            ];
        }
        if ($data) {
            $this->lineItemsRepository->update($data, $this->context);
        }

        if (isset($this->items['shipping'])) {
            $customFields = $this->order->getCustomFields();
            $ratepayCustomFields = $customFields['ratepay']['shipping'] ?? LineItemUtil::getEmptyCustomFields();
            $this->updateCustomField($ratepayCustomFields, $this->items['shipping']);
            $customFields['ratepay']['shipping'] = $ratepayCustomFields;

            $this->orderRepository->update([
                [
                    'id' => $this->order->getId(),
                    'customFields' => $customFields
                ]
            ], $this->context);
        }

        if ($this->updateStock) {
            $this->updateProductStocks();
        }
    }

    protected abstract function updateCustomField(array &$customFields, $qty);

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
            $this->productRepository->update($data, $this->context);
        } catch (Exception $e) {
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
