<?php

declare(strict_types=1);
/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\Shopware6\Tests\Unit\Components\StateMachine\Subscriber;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\OrderOperationData;
use Ratepay\RpayPayments\Components\StateMachine\Subscriber\PaymentStatusSubscriber;
use Ratepay\RpayPayments\Core\Entity\Extension\OrderExtension;
use Ratepay\RpayPayments\Core\Entity\Extension\OrderLineItemExtension;
use Ratepay\RpayPayments\Core\Entity\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Core\Entity\RatepayOrderLineItemDataEntity;
use Ratepay\RpayPayments\Core\Entity\RatepayPositionEntity;
use Ratepay\RpayPayments\Core\Event\OrderItemOperationDoneEvent;
use Ratepay\RpayPayments\Core\PluginConfigService;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;

class PaymentStatusSubscriberTest extends TestCase
{
    private readonly PluginConfigService $configService;

    protected function setUp(): void
    {
        $this->configService = $this->createMock(PluginConfigService::class);
        $this->configService->method('isUpdatePaymentStatusOnOrderItemOperation')->willReturn(true);
    }

    public function testIfConfigCanDisableFunktion()
    {
        $configService = $this->createMock(PluginConfigService::class);
        $configService->method('isUpdatePaymentStatusOnOrderItemOperation')->willReturn(false);

        $event = $this->createOrderOperationDoneEvent([
            $this->getLineItem(5, 0, 5, 0),
            $this->getLineItem(10, 0, 10, 0),
            $this->getLineItem(15, 0, 15, 0),
        ]);

        $transactionStateHandler = $this->createTransactionHandlerMock(null);
        (new PaymentStatusSubscriber($transactionStateHandler, $configService))->onItemsOperationDone($event);
    }

    public function testSimpleFullCancel()
    {
        $event = $this->createOrderOperationDoneEvent([
            $this->getLineItem(5, 0, 5, 0),
            $this->getLineItem(10, 0, 10, 0),
            $this->getLineItem(15, 0, 15, 0),
        ]);

        $transactionStateHandler = $this->createTransactionHandlerMock('cancel');
        (new PaymentStatusSubscriber($transactionStateHandler, $this->configService))->onItemsOperationDone($event);
    }

    /**
     * @dataProvider dataProviderPartlyPaid
     */
    public function testPartlyPayed(array ...$items)
    {
        $that = $this;
        $event = $this->createOrderOperationDoneEvent(array_map(static fn (array $item) => call_user_func_array([$that, 'getLineItem'], $item), $items));

        $transactionStateHandler = $this->createTransactionHandlerMock('payPartially');
        (new PaymentStatusSubscriber($transactionStateHandler, $this->configService))->onItemsOperationDone($event);
    }

    public static function dataProviderPartlyPaid(): array
    {
        return [
            [
                [5, 4, 0, 0], // 4 delivered, 0 canceled, 0 returned, 1 left
                [10, 6, 0, 0], // 6 delivered, 0 canceled, 0 returned, 4 left
                [15, 8, 0, 0], // 8 delivered, 0 canceled, 0 returned, 7 left
            ],
            [
                [5, 4, 0, 0], // 4 delivered, 0 canceled, 0 returned, 1 left
                [10, 1, 9, 0], // 1 delivered, 9 canceled, 0 returned, 0 left
                [15, 1, 14, 0], // 1 delivered, 14 canceled, 0 returned, 0 left
            ],
            [
                [5, 2, 1, 1], // 2 delivered, 1 canceled, 1 returned, 1 left
                [10, 1, 9, 0], // 1 delivered, 9 canceled, 0 returned, 0 left
                [15, 1, 14, 0], // 1 delivered, 14 canceled, 0 returned, 0 left
            ],
            [
                [5, 0, 0, 0], // 0 delivered, 0 canceled, 0 returned, 5 left
                [10, 0, 0, 0], // 0 delivered, 0 canceled, 0 returned, 10 left
                [15, 1, 0, 0], // 1 delivered, 0 canceled, 0 returned, 14 left
            ],
            [
                [5, 0, 0, 0], // 0 delivered, 0 canceled, 0 returned, 5 left
                [10, 0, 0, 0], // 0 delivered, 0 canceled, 0 returned, 10 left
                [15, 1, 10, 0], // 1 delivered, 10 canceled, 0 returned, 3 left
            ],
            [
                [5, 0, 0, 0], // 0 delivered, 0 canceled, 0 returned, 5 left
                [10, 0, 0, 0], // 0 delivered, 0 canceled, 0 returned, 10 left
                [15, 2, 0, 2], // 2 delivered, 0 canceled, 2 returned, 13 left
            ],
        ];
    }

    /**
     * @dataProvider dataProviderFullPaid
     */
    public function testFullPaid(array ...$items)
    {
        $that = $this;
        $event = $this->createOrderOperationDoneEvent(array_map(static fn (array $item) => call_user_func_array([$that, 'getLineItem'], $item), $items));

        $transactionStateHandler = $this->createTransactionHandlerMock('paid');
        (new PaymentStatusSubscriber($transactionStateHandler, $this->configService))->onItemsOperationDone($event);
    }

    public static function dataProviderFullPaid(): array
    {
        return [
            [
                [5, 5, 0, 0], // 5 delivered, 0 canceled, 0 returned, 0 left
                [10, 10, 0, 0], // 10 delivered, 0 canceled, 0 returned, 0 left
                [15, 15, 0, 0], // 15 delivered, 0 canceled, 0 returned, 0 left
            ],
            [
                [5, 4, 1, 0], // 4 delivered, 1 canceled, 0 returned, 0 left
                [10, 10, 0, 0], // 10 delivered, 0 canceled, 0 returned, 0 left
                [15, 15, 0, 0], // 15 delivered, 0 canceled, 0 returned, 0 left
            ],
            [
                [5, 4, 1, 0], // 4 delivered, 1 canceled, 0 returned, 0 left
                [10, 6, 4, 0], // 6 delivered, 4 canceled, 0 returned, 0 left
                [15, 8, 7, 0], // 8 delivered, 7 canceled, 0 returned, 0 left
            ],
        ];
    }

    /**
     * @dataProvider dataFullRefunded
     */
    public function testFullRefunded(array ...$items)
    {
        $that = $this;
        $event = $this->createOrderOperationDoneEvent(array_map(static fn (array $item) => call_user_func_array([$that, 'getLineItem'], $item), $items));

        $transactionStateHandler = $this->createTransactionHandlerMock('refund');
        (new PaymentStatusSubscriber($transactionStateHandler, $this->configService))->onItemsOperationDone($event);
    }

    public static function dataFullRefunded(): array
    {
        return [
            [
                [5, 5, 0, 5], // 5 delivered, 0 canceled, 1 returned, 0 left
                [10, 10, 0, 10], // 10 delivered, 0 canceled, 10 returned, 0 left
                [15, 15, 0, 15], // 15 delivered, 0 canceled, 15 returned, 0 left
            ],
            [
                [5, 1, 4, 1], // 1 delivered, 4 canceled, 1 returned, 0 left
                [10, 5, 5, 5], // 5 delivered, 5 canceled, 5 returned, 0 left
                [15, 5, 10, 5], // 5 delivered, 10 canceled, 5 returned, 0 left
            ],
        ];
    }

    /**
     * @dataProvider dataProviderPartlyRefunded
     */
    public function testPartlyRefunded(array ...$items)
    {
        $that = $this;
        $event = $this->createOrderOperationDoneEvent(array_map(static fn (array $item) => call_user_func_array([$that, 'getLineItem'], $item), $items));

        $transactionStateHandler = $this->createTransactionHandlerMock('refundPartially');
        (new PaymentStatusSubscriber($transactionStateHandler, $this->configService))->onItemsOperationDone($event);
    }

    public static function dataProviderPartlyRefunded(): array
    {
        return [
            [
                [5, 5, 0, 1], // 5 delivered, 0 canceled, 1 returned, 0 left
                [10, 10, 0, 0], // 10 delivered, 0 canceled, 0 returned, 0 left
                [15, 15, 0, 0], // 15 delivered, 0 canceled, 0 returned, 0 left
            ],
            [
                [5, 5, 0, 1], // 5 delivered, 0 canceled, 1 returned, 0 left
                [10, 10, 0, 1], // 10 delivered, 0 canceled, 1 returned, 0 left
                [15, 15, 0, 1], // 15 delivered, 0 canceled, 1 returned, 0 left
            ],
            [
                [5, 5, 0, 1], // 5 delivered, 0 canceled, 1 returned, 0 left
                [10, 6, 4, 2], // 6 delivered, 4 canceled, 2 returned, 0 left
                [15, 10, 5, 3], // 10 delivered, 5 canceled, 3 returned, 0 left
            ],
        ];
    }

    /**
     * @dataProvider dataProviderShipping
     */
    public function testIfShippingGotHandledCorrectly(int $delivered, int $canceled, int $refunded, string $expectedMethod = null)
    {
        $event = $this->createOrderOperationDoneEvent([
            $this->getLineItem(5, 0, 5, 0),
            $this->getLineItem(10, 0, 10, 0),
            $this->getLineItem(15, 0, 15, 0),
        ]);
        /** @var RatepayOrderDataEntity $ratepayData */
        $ratepayData = $event->getOrderEntity()->getExtension(OrderExtension::EXTENSION_NAME);
        $ratepayData->assign([
            RatepayOrderDataEntity::FIELD_SHIPPING_POSITION => $this->createPosition($delivered, $canceled, $refunded),
        ]);

        $transactionStateHandler = $this->createTransactionHandlerMock($expectedMethod);
        (new PaymentStatusSubscriber($transactionStateHandler, $this->configService))->onItemsOperationDone($event);
    }

    public static function dataProviderShipping(): array
    {
        return [
            [1, 0, 0, 'paid'],
            [0, 1, 0, 'cancel'],
            [1, 0, 1, 'refund'],
            [0, 0, 0, null],
        ];
    }

    /**
     * @depends testFullRefunded
     */
    public function testIfNoRatepayItemsGotProcessedCorrectly()
    {
        $event = $this->createOrderOperationDoneEvent([
            $this->getLineItem(5, 5, 0, 5),
            $this->getLineItem(10, 10, 0, 10),
            $this->getLineItem(15, 0, 0, 0),
        ]);

        // test if refunded got still called
        $event->getOrderEntity()->getLineItems()->last()->setExtensions([]); // remove position from last item.
        $transactionStateHandler = $this->createTransactionHandlerMock('refund');
        (new PaymentStatusSubscriber($transactionStateHandler, $this->configService))->onItemsOperationDone($event);

        // test if subscriber does not change the state of the payment.
        // remove all ratepay data from order-line-items
        $event->getOrderEntity()->getLineItems()->map(static fn (OrderLineItemEntity $item) => $item->setExtensions([]));
        $transactionStateHandler = $this->createTransactionHandlerMock(null);
        (new PaymentStatusSubscriber($transactionStateHandler, $this->configService))->onItemsOperationDone($event);
    }

    private function getLineItem(int $qty, int $delivered, int $canceled, int $refunded): OrderLineItemEntity
    {
        return (new OrderLineItemEntity())->assign([
            'id' => Uuid::randomHex(),
            'quantity' => $qty,
            'extensions' => [
                OrderLineItemExtension::EXTENSION_NAME => (new RatepayOrderLineItemDataEntity())->assign([
                    RatepayOrderLineItemDataEntity::FIELD_POSITION => $this->createPosition($delivered, $canceled, $refunded),
                ]),
            ],
        ]);
    }

    private function createPosition(int $delivered, int $canceled, int $refunded): RatepayPositionEntity
    {
        return (new RatepayPositionEntity())->assign([
            RatepayPositionEntity::FIELD_DELIVERED => $delivered,
            RatepayPositionEntity::FIELD_CANCELED => $canceled,
            RatepayPositionEntity::FIELD_RETURNED => $refunded,
        ]);
    }

    private function createOrderOperationDoneEvent(array $lineItems): OrderItemOperationDoneEvent
    {
        $orderEntity = new OrderEntity();
        $orderEntity->assign([
            'id' => Uuid::randomHex(),
            'lineItems' => new OrderLineItemCollection($lineItems),
            'transactions' => new OrderTransactionCollection([
                (new OrderTransactionEntity())->assign([
                    'id' => Uuid::randomHex(),
                ]),
            ]),
            'extensions' => [
                OrderExtension::EXTENSION_NAME => new RatepayOrderDataEntity(),
            ],
        ]);

        $orderOperationData = new OrderOperationData(Context::createDefaultContext(), $orderEntity, '--', [], false);
        return new OrderItemOperationDoneEvent($orderEntity, $orderOperationData, $orderOperationData->getContext());
    }

    /**
     * @return OrderTransactionStateHandler&MockObject
     */
    private function createTransactionHandlerMock(string $expectedMethod = null)
    {
        $transactionStateHandler = $this->createMock(OrderTransactionStateHandler::class);

        $expectedMethod !== 'cancel' && $transactionStateHandler->expects($this->never())->method('cancel');

        $expectedMethod !== 'paid' && $transactionStateHandler->expects($this->never())->method('paid');
        $expectedMethod !== 'payPartially' && $transactionStateHandler->expects($this->never())->method('payPartially');

        $expectedMethod !== 'refundPartially' && $transactionStateHandler->expects($this->never())->method('refundPartially');
        $expectedMethod !== 'refund' && $transactionStateHandler->expects($this->never())->method('refund');

        $expectedMethod !== null && $transactionStateHandler->expects($this->once())->method($expectedMethod);
        $expectedMethod === null && $transactionStateHandler->expects($this->never())->method($this->anything());

        return $transactionStateHandler;
    }
}
