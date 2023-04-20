<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Components\OrderManagement\Service;


use Ratepay\RpayPayments\Components\OrderManagement\Exception\TaxRuleNotFoundException;
use Shopware\Core\Checkout\Cart\Calculator;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Order\IdStruct;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRule;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;

class LineItemFactory
{

    private OrderConverter $orderConverter;

    private Calculator $calculator;

    public function __construct(
        OrderConverter $orderConverter,
        Calculator $calculator
    )
    {
        $this->orderConverter = $orderConverter;
        $this->calculator = $calculator;
    }

    /**
     * @param float|string $taxRuleIdOrTaxRate
     */
    public function createLineItem(OrderEntity $orderEntity, string $label, float $grossAmount, $taxRuleIdOrTaxRate, Context $context): LineItem
    {
        $salesChannelContext = $this->orderConverter->assembleSalesChannelContext($orderEntity, $context);

        if (is_numeric($taxRuleIdOrTaxRate)) {
            $taxRate = $taxRuleIdOrTaxRate;
        } else {
            $taxRule = $salesChannelContext->getTaxRules()->get($taxRuleIdOrTaxRate)->getRules()->first();
            if ($taxRule === null) {
                throw new TaxRuleNotFoundException();
            }

            $taxRate = $taxRule->getTaxRate();
        }

        $lineItem = (new LineItem(Uuid::randomHex(), LineItem::CUSTOM_LINE_ITEM_TYPE, null, 1))
            ->setStackable(false)
            ->setRemovable(false)
            ->setLabel($label)
            ->setDescription($label)
            ->setPayload([])
            ->setPriceDefinition(new QuantityPriceDefinition(
                $grossAmount,
                new TaxRuleCollection([new TaxRule($taxRate, 100)]),
                1
            ));
        $lineItem->addExtension(OrderConverter::ORIGINAL_ID, new IdStruct($lineItem->getId()));

        $updatedItems = $this->calculator->calculate(
            new LineItemCollection([$lineItem]),
            $salesChannelContext,
            new CartBehavior($salesChannelContext->getPermissions())
        );

        return $updatedItems->first();
    }

}
