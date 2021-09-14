<?php declare(strict_types=1);


namespace Ratepay\RpayPayments\Components\Checkout\Event;


use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class PaymentDataExtensionBuilt extends Event
{

    private ArrayStruct $extension;

    private SalesChannelContext $salesChannelContext;

    private ?OrderEntity $orderEntity;

    public function __construct(ArrayStruct $extension, SalesChannelContext $salesChannelContext, OrderEntity $orderEntity = null)
    {
        $this->extension = $extension;
        $this->salesChannelContext = $salesChannelContext;
        $this->orderEntity = $orderEntity;
    }

    public function getExtension(): ArrayStruct
    {
        return $this->extension;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function getOrderEntity(): ?OrderEntity
    {
        return $this->orderEntity;
    }

}
