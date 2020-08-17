<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Dto;


use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Order\IdStruct;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Uuid\Uuid;

class AddCreditData extends OrderOperationData
{

    public function __construct(
        OrderEntity $order,
        string $label,
        float $grossAmount,
        float $taxRate
    ) {
        $lineItem = (new LineItem(Uuid::randomHex(), LineItem::CUSTOM_LINE_ITEM_TYPE, null, 1))
            ->setStackable(false)
            ->setRemovable(false)
            ->setLabel($label)
            ->setDescription($label)
            ->setPayload([])
            ->setPriceDefinition(QuantityPriceDefinition::fromArray([
                'isCalculated' => true,
                'price' => $grossAmount,
                'quantity' => 1,
                'precision' => $order->getCurrency()->getDecimalPrecision(),
                'taxRules' => [
                    [
                        'taxRate' => $taxRate,
                        'percentage' => 100,
                    ],
                ]
            ]));
        $lineItem->addExtension(OrderConverter::ORIGINAL_ID, new IdStruct($lineItem->getId()));

        parent::__construct($order, self::OPERATION_ADD, [$lineItem], false);
    }

}
