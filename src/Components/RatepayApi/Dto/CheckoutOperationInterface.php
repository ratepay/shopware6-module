<?php declare(strict_types=1);

namespace Ratepay\RpayPayments\Components\RatepayApi\Dto;

use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface CheckoutOperationInterface
{

    public function getPaymentMethodId(): string;

    public function getSalesChannelContext(): SalesChannelContext;

}
