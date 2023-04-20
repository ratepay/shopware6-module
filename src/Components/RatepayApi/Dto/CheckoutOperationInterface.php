<?php declare(strict_types=1);

namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;

use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
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
