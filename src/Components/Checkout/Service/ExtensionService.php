<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Service;

use Ratepay\RpayPayments\Components\Checkout\Event\PaymentDataExtensionBuilt;
use Ratepay\RpayPayments\Components\Checkout\Model\Collection\RatepayOrderLineItemDataCollection;
use Ratepay\RpayPayments\Components\Checkout\Model\Definition\RatepayOrderDataDefinition;
use Ratepay\RpayPayments\Components\Checkout\Model\Definition\RatepayOrderLineItemDataDefinition;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderLineItemDataEntity;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayPositionEntity;
use Ratepay\RpayPayments\Components\Checkout\Util\BankAccountHolderHelper;
use Ratepay\RpayPayments\Components\InstallmentCalculator\Service\InstallmentService;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\ProfileConfigService;
use Ratepay\RpayPayments\Components\RatepayApi\Service\TransactionIdService;
use Ratepay\RpayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ExtensionService
{
    public const PAYMENT_PAGE_EXTENSION_NAME = 'ratepay';

    private EntityRepositoryInterface $orderExtensionRepository;

    private EntityRepositoryInterface $lineItemExtensionRepository;

    private InstallmentService $installmentService;

    private TransactionIdService $transactionIdService;

    private ProfileConfigService $profileConfigService;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityRepositoryInterface $orderExtensionRepository,
        EntityRepositoryInterface $lineItemExtensionRepository,
        InstallmentService $installmentService,
        TransactionIdService $transactionIdService,
        ProfileConfigService $profileConfigService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->orderExtensionRepository = $orderExtensionRepository;
        $this->lineItemExtensionRepository = $lineItemExtensionRepository;
        $this->installmentService = $installmentService;
        $this->transactionIdService = $transactionIdService;
        $this->profileConfigService = $profileConfigService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createLineItemExtensionEntities(
        array $lineItems,
        Context $context
    ): RatepayOrderLineItemDataCollection {
        $data = [];
        foreach ($lineItems as $lineItem) {
            $data[] = [
                RatepayOrderLineItemDataEntity::FIELD_ORDER_LINE_ITEM_ID => $lineItem->getId(),
                RatepayOrderLineItemDataEntity::FIELD_ORDER_LINE_ITEM_VERSION_ID => $lineItem->getVersionId(),
                RatepayOrderLineItemDataEntity::FIELD_POSITION => [
                    RatepayPositionEntity::FIELD_ID => Uuid::randomHex(),
                ],
            ];
        }
        $event = $this->lineItemExtensionRepository->upsert($data, $context);

        $affected = $this->lineItemExtensionRepository->search(new Criteria(
            $event->getPrimaryKeys(RatepayOrderLineItemDataDefinition::ENTITY_NAME)
        ), $context);

        /** @var RatepayOrderLineItemDataCollection $collection */
        $collection = $affected->getEntities();

        return $collection;
    }

    public function createOrderExtensionEntity(
        OrderEntity $order,
        string $transactionId = null,
        string $descriptor = null,
        string $profileId = null,
        bool $successful,
        Context $context
    ): RatepayOrderDataEntity {
        $orderExtensionData = [
            RatepayOrderDataEntity::FIELD_ORDER_ID => $order->getId(),
            RatepayOrderDataEntity::FIELD_ORDER_VERSION_ID => $order->getVersionId(),
            RatepayOrderDataEntity::FIELD_PROFILE_ID => $profileId,
            RatepayOrderDataEntity::FIELD_TRANSACTION_ID => $transactionId,
            RatepayOrderDataEntity::FIELD_DESCRIPTOR => $descriptor,
            RatepayOrderDataEntity::FIELD_SUCCESSFUL => $successful,
        ];

        if ($successful && $order->getShippingCosts()->getTotalPrice() > 0) {
            $orderExtensionData[RatepayOrderDataEntity::FIELD_SHIPPING_POSITION] = [
                RatepayPositionEntity::FIELD_ID => Uuid::randomHex(),
            ];
        }

        // check if an entry already exists, e.g. after a failed payment
        $criteria = new Criteria();
        foreach ([RatepayOrderDataEntity::FIELD_ORDER_ID, RatepayOrderDataEntity::FIELD_ORDER_VERSION_ID] as $filterKey) {
            $criteria->addFilter(new EqualsFilter($filterKey, $orderExtensionData[$filterKey]));
        }
        $ids = $this->orderExtensionRepository->searchIds($criteria, $context);
        if ($ids->firstId()) {
            $orderExtensionData[RatepayOrderDataEntity::FIELD_ID] = $ids->firstId();
        }

        $event = $this->orderExtensionRepository->upsert([$orderExtensionData], $context);

        $affected = $this->orderExtensionRepository->search(new Criteria(
            $event->getPrimaryKeys(RatepayOrderDataDefinition::ENTITY_NAME)
        ), $context);

        return $affected->first();
    }

    public function buildPaymentDataExtension(
        SalesChannelContext $salesChannelContext,
        ?OrderEntity $order = null
    ): ?ArrayStruct {
        $paymentMethod = $salesChannelContext->getPaymentMethod();

        $profileConfig = $this->profileConfigService->getProfileConfigBySalesChannel($salesChannelContext, $paymentMethod->getId());
        if ($profileConfig === null) {
            // should never occur
            return null;
        }

        $customer = $salesChannelContext->getCustomer();

        if ($customer) {
            $customerBirthday = $customer->getBirthday();
            $customerBillingAddress = $customer->getActiveBillingAddress();
            if ($customerBillingAddress) {
                $vatIds = $customer->getVatIds();
                $customerVatId = $vatIds[0] ?? null;
                $customerPhoneNumber = $customerBillingAddress->getPhoneNumber();
                $customerCompany = $customerBillingAddress->getCompany();
                $accountHolders = BankAccountHolderHelper::getAvailableNames($salesChannelContext);
            }
        }


        $extension = new ArrayStruct();
        $extension->offsetSet('isSandbox', $profileConfig->isSandbox());
        $extension->offsetSet('birthday', $customerBirthday ?? null);
        $extension->offsetSet('vatId', $customerVatId ?? null);
        $extension->offsetSet('phoneNumber', $customerPhoneNumber ?? null);
        $extension->offsetSet('company', $customerCompany ?? null);
        $extension->offsetSet('accountHolders', $accountHolders ?? null);
        $extension->offsetSet(
            'paymentMethod',
            strtolower(constant($paymentMethod->getHandlerIdentifier() . '::RATEPAY_METHOD'))
        );

        if (!MethodHelper::isInstallmentMethod($paymentMethod->getHandlerIdentifier())) {
            // only set transaction ID for payment methods which are not a installment.
            // for installment we generate a new transaction ID when the runtime has been selected.
            // this transaction ID will be send to the storefront separately.
            $transactionId = $this->transactionIdService->getTransactionId(
                $salesChannelContext,
                $order ? TransactionIdService::PREFIX_ORDER . $order->getId() . '-' : TransactionIdService::PREFIX_CART
            );
            $extension->offsetSet('transactionId', $transactionId);
        }

        /** @var PaymentDataExtensionBuilt $event */
        $event = $this->eventDispatcher->dispatch(new PaymentDataExtensionBuilt($extension, $salesChannelContext, $order));

        return $event->getExtension();
    }
}
