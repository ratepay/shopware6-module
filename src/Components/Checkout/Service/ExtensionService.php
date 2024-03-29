<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Service;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use DateTime;
use Ratepay\RpayPayments\Components\Checkout\Event\OrderExtensionDataBuilt;
use Ratepay\RpayPayments\Components\Checkout\Event\PaymentDataExtensionBuilt;
use Ratepay\RpayPayments\Components\Checkout\Model\Collection\RatepayOrderLineItemDataCollection;
use Ratepay\RpayPayments\Components\Checkout\Model\Definition\RatepayOrderDataDefinition;
use Ratepay\RpayPayments\Components\Checkout\Model\Definition\RatepayOrderLineItemDataDefinition;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayOrderLineItemDataEntity;
use Ratepay\RpayPayments\Components\Checkout\Model\RatepayPositionEntity;
use Ratepay\RpayPayments\Components\Checkout\Util\BankAccountHolderHelper;
use Ratepay\RpayPayments\Components\ProfileConfig\Exception\ProfileNotFoundException;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileByOrderEntity;
use Ratepay\RpayPayments\Components\ProfileConfig\Service\Search\ProfileBySalesChannelContext;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Service\TransactionIdService;
use Ratepay\RpayPayments\Util\MethodHelper;
use Ratepay\RpayPayments\Util\RequestHelper;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class ExtensionService
{
    /**
     * @var string
     */
    final public const PAYMENT_PAGE_EXTENSION_NAME = 'ratepay';

    public function __construct(
        private readonly EntityRepository $orderExtensionRepository,
        private readonly EntityRepository $lineItemExtensionRepository,
        private readonly TransactionIdService $transactionIdService,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ProfileBySalesChannelContext $profileBySalesChannelContext,
        private readonly ProfileByOrderEntity $profileByOrderEntity
    ) {
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
        PaymentRequestData $requestData,
        ?string $transactionId,
        ?string $descriptor,
        bool $successful
    ): RatepayOrderDataEntity {
        $order = $requestData->getOrder();
        $context = $requestData->getContext();

        $orderExtensionData = [
            RatepayOrderDataEntity::FIELD_ORDER_ID => $order->getId(),
            RatepayOrderDataEntity::FIELD_ORDER_VERSION_ID => $order->getVersionId(),
            RatepayOrderDataEntity::FIELD_PROFILE_ID => $requestData->getProfileConfig()->getProfileId(),
            RatepayOrderDataEntity::FIELD_TRANSACTION_ID => $transactionId,
            RatepayOrderDataEntity::FIELD_DESCRIPTOR => $descriptor,
            RatepayOrderDataEntity::FIELD_SUCCESSFUL => $successful,
            RatepayOrderDataEntity::FIELD_ADDITIONAL_DATA => [],
            RatepayOrderDataEntity::FIELD_SEND_DISCOUNT_AS_CART_ITEM => $requestData->isSendDiscountAsCartItem(),
            RatepayOrderDataEntity::FIELD_SEND_SHIPPING_COSTS_AS_CART_ITEM => $requestData->isSendShippingCostsAsCartItem(),
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

        /** @var OrderExtensionDataBuilt $event */
        $event = $this->eventDispatcher->dispatch(new OrderExtensionDataBuilt($order, $requestData, $orderExtensionData));
        $orderExtensionData = $event->getData();

        $event = $this->orderExtensionRepository->upsert([$orderExtensionData], $context);

        $affected = $this->orderExtensionRepository->search(new Criteria(
            $event->getPrimaryKeys(RatepayOrderDataDefinition::ENTITY_NAME)
        ), $context);

        return $affected->first();
    }

    public function buildPaymentDataExtension(
        SalesChannelContext $salesChannelContext,
        ?OrderEntity $order = null,
        Request $httpRequest = null
    ): ?ArrayStruct {
        $paymentMethod = $salesChannelContext->getPaymentMethod();

        $searchService = $order instanceof OrderEntity ? $this->profileByOrderEntity : $this->profileBySalesChannelContext;
        $profileConfig = $searchService->search(
            $searchService->createSearchObject($order ?? $salesChannelContext)->setPaymentMethodId($paymentMethod->getId())
        )->first();

        if (!$profileConfig instanceof Entity) {
            // should never occur
            throw new ProfileNotFoundException();
        }

        $customer = $salesChannelContext->getCustomer();

        if ($customer instanceof CustomerEntity) {
            $customerBirthday = $customer->getBirthday();
            $customerBillingAddress = $customer->getActiveBillingAddress();
            if ($customerBillingAddress instanceof CustomerAddressEntity) {
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
            strtolower((string) constant($paymentMethod->getHandlerIdentifier() . '::RATEPAY_METHOD'))
        );

        if (!MethodHelper::isInstallmentMethod($paymentMethod->getHandlerIdentifier())) {
            // only set transaction ID for payment methods which are not an installment.
            // for installment, we generate a new transaction ID when the runtime has been selected.
            // this transaction ID will be sent to the storefront separately.
            $transactionId = $this->transactionIdService->getTransactionId(
                $salesChannelContext,
                $order instanceof OrderEntity ? TransactionIdService::PREFIX_ORDER . $order->getId() . '-' : TransactionIdService::PREFIX_CART,
                $profileConfig
            );
            $extension->offsetSet('transactionId', $transactionId);
        }

        if ($httpRequest instanceof Request) {
            // add user entered values again, so that the user have not to reenter his values
            foreach (RequestHelper::getArray($httpRequest, 'ratepay') ?: [] as $key => $value) {
                if ($key === 'birthday' && is_array($value)) {
                    $value = (new DateTime())->setDate((int) $value['year'], (int) $value['month'], (int) $value['day']);
                }

                $extension->set($key, $value);
            }
        }

        /** @var PaymentDataExtensionBuilt $event */
        $event = $this->eventDispatcher->dispatch(new PaymentDataExtensionBuilt($extension, $salesChannelContext, $order));

        return $event->getExtension();
    }
}
