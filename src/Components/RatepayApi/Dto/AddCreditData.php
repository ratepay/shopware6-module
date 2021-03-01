<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Order\IdStruct;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;

class AddCreditData extends OrderOperationData
{
    public function __construct(
        Context $context,
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
            ->setPriceDefinition(new QuantityPriceDefinition(
                $grossAmount,
                new TaxRuleCollection([new TaxRule($taxRate, 100)]),
                $order->getCurrency()->getDecimalPrecision(),
                1,
                true)
            );
        $lineItem->addExtension(OrderConverter::ORIGINAL_ID, new IdStruct($lineItem->getId()));

        parent::__construct($context, $order, self::OPERATION_ADD, [$lineItem], false);
    }
}
