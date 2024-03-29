<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;

class AddCreditData extends OrderOperationData
{
    /**
     * @param LineItem[] $items
     */
    public function __construct(Context $context, OrderEntity $order, array $items = [])
    {
        parent::__construct($context, $order, self::OPERATION_ADD, $items, false);
    }

    public function addItem(LineItem $item): self
    {
        $this->items[] = $item;

        return $this;
    }
}
