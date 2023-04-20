<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface CheckoutOperationInterface
{
    public function getPaymentMethodId(): string;

    public function getSalesChannelContext(): SalesChannelContext;

    public function getRequestDataBag(): DataBag;

    /**
     * @return OrderCustomerEntity|CustomerEntity
     */
    public function getCustomer();
}
