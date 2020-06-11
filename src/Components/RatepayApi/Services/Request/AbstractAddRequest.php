<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


use BadMethodCallException;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\FileLogger;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\HistoryLogger;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\RequestLogger;
use Ratepay\RatepayPayments\Core\PluginConfig\Services\ConfigService;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigRepository;
use Ratepay\RatepayPayments\Util\CriteriaHelper;
use RatePAY\RequestBuilder;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Order\IdStruct;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Shopware\Core\Checkout\Cart\Order\RecalculationService;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Uuid\Uuid;

abstract class AbstractAddRequest extends AbstractModifyRequest
{

    /**
     * @var RecalculationService
     */
    private $recalculationService;

    public function __construct(
        ConfigService $configService,
        HeadFactory $headFactory,
        ShoppingBasketFactory $shoppingBasketFactory,
        ProfileConfigRepository $profileConfigRepository,
        EntityRepositoryInterface $productRepository,
        EntityRepositoryInterface $orderRepository,
        EntityRepositoryInterface $lineItemsRepository,
        RecalculationService $recalculationService,
        RequestLogger $requestLogger,
        FileLogger $fileLogger,
        HistoryLogger $historyLogger
    )
    {
        parent::__construct($configService, $headFactory, $shoppingBasketFactory, $profileConfigRepository, $productRepository, $orderRepository, $lineItemsRepository, $requestLogger, $fileLogger, $historyLogger);
        $this->recalculationService = $recalculationService;
    }

    /**
     * @param string $label
     * @param float $amount
     */
    public function setAmount(string $label, float $amount): void
    {
        $lineItem = (new LineItem(Uuid::randomHex(), LineItem::CUSTOM_LINE_ITEM_TYPE, null, 1))
            ->setStackable(false)
            ->setRemovable(false)
            ->setLabel($label)
            ->setDescription($label)
            ->setPayload([])
            ->setPriceDefinition(QuantityPriceDefinition::fromArray([
                'isCalculated' => false,
                'price' => $amount,
                'quantity' => 1,
                'precision' => $this->context->getCurrencyPrecision(),
                'taxRules' => [
                    [
                        'taxRate' => 0,
                        'percentage' => 100
                    ]
                ]
            ]));

        $lineItem->addExtension(OrderConverter::ORIGINAL_ID, new IdStruct($lineItem->getId()));

        parent::setItems([
            $lineItem
        ]);
    }

    /**
     * @param array $items
     */
    public function setItems(array $items): void
    {
        throw new BadMethodCallException('do not call this function. please use the `setAmount` method!');
    }


    protected function processSuccess(RequestBuilder $response)
    {
        $newItems = [];

        $versionId = $this->orderRepository->createVersion($this->order->getId(), $this->context);
        $versionContext = $this->context->createWithVersionId($versionId);
        /** @var LineItem $item */
        foreach ($this->items as $item) {
            $this->recalculationService->addCustomLineItem($this->order->getId(), $item, $versionContext);
            $newItems[$item->getId()] = $item->getPriceDefinition()->getQuantity();
        }
        $this->orderRepository->merge($versionId, $this->context);

        $this->order = $this->orderRepository->search(CriteriaHelper::getCriteriaForOrder($this->order->getId()), $this->context)->first();
        $this->items = $newItems;
        parent::processSuccess($response);
    }

    protected function updateCustomField(array &$customFields, $qty)
    {
        $customFields['delivered'] = $customFields['delivered'] + $qty;
    }
}
