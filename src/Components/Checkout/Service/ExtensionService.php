<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Service;


use Ratepay\RatepayPayments\Components\Checkout\Model\Collection\RatepayOrderLineItemDataCollection;
use Ratepay\RatepayPayments\Components\Checkout\Model\Definition\RatepayOrderDataDefinition;
use Ratepay\RatepayPayments\Components\Checkout\Model\Definition\RatepayOrderLineItemDataDefinition;
use Ratepay\RatepayPayments\Components\Checkout\Model\RatepayOrderDataEntity;
use Ratepay\RatepayPayments\Components\Checkout\Model\RatepayOrderLineItemDataEntity;
use Ratepay\RatepayPayments\Components\Checkout\Model\RatepayPositionEntity;
use Ratepay\RatepayPayments\Components\InstallmentCalculator\Service\InstallmentService;
use Ratepay\RatepayPayments\Util\MethodHelper;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ExtensionService
{

    /**
     * @var EntityRepositoryInterface
     */
    private $orderExtensionRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $lineItemExtensionRepository;

    /**
     * @var InstallmentService
     */
    private $installmentService;

    public function __construct(
        EntityRepositoryInterface $orderExtensionRepository,
        EntityRepositoryInterface $lineItemExtensionRepository,
        InstallmentService $installmentService
    )
    {
        $this->orderExtensionRepository = $orderExtensionRepository;
        $this->lineItemExtensionRepository = $lineItemExtensionRepository;
        $this->installmentService = $installmentService;
    }

    /**
     * @param OrderLineItemEntity[] $lineItems
     * @param Context $context
     * @return RatepayOrderLineItemDataCollection
     */
    public function createLineItemExtensions(array $lineItems, Context $context): RatepayOrderLineItemDataCollection
    {
        $data = [];
        foreach ($lineItems as $lineItem) {
            $data[] = [
                RatepayOrderLineItemDataEntity::FIELD_ORDER_LINE_ITEM_ID => $lineItem->getId(),
                RatepayOrderLineItemDataEntity::FIELD_ORDER_LINE_ITEM_VERSION_ID => $lineItem->getVersionId(),
                RatepayOrderLineItemDataEntity::FIELD_POSITION => [
                    RatepayPositionEntity::FIELD_ID => Uuid::randomHex()
                ]
            ];
        }
        $event = $this->lineItemExtensionRepository->upsert($data, $context);

        $affected = $this->lineItemExtensionRepository->search(new Criteria(
            $event->getPrimaryKeys(RatepayOrderLineItemDataDefinition::ENTITY_NAME)
        ), $context);

        return $affected->getEntities();
    }

    /**
     * @param OrderEntity $order
     * @param string $transactionId
     * @param string $profileId
     * @param Context $context
     * @return RatepayOrderDataEntity
     */
    public function createOrderExtension(OrderEntity $order, string $transactionId, string $profileId, Context $context): RatepayOrderDataEntity
    {
        $event = $this->orderExtensionRepository->create([[
            RatepayOrderDataEntity::FIELD_ORDER_ID => $order->getId(),
            RatepayOrderDataEntity::FIELD_ORDER_VERSION_ID => $order->getVersionId(),
            RatepayOrderDataEntity::FIELD_PROFILE_ID => $profileId,
            RatepayOrderDataEntity::FIELD_TRANSACTION_ID => $transactionId,
            RatepayOrderDataEntity::FIELD_SHIPPING_POSITION => [
                RatepayPositionEntity::FIELD_ID => Uuid::randomHex()
            ]
        ]], $context);

        $affected = $this->orderExtensionRepository->search(new Criteria(
            $event->getPrimaryKeys(RatepayOrderDataDefinition::ENTITY_NAME)
        ), $context);

        return $affected->first();
    }

    public function buildPaymentDataExtension(
        SalesChannelContext $salesChannelContext,
        ?OrderEntity $order = null
    ): ArrayStruct
    {
        $paymentMethod = $salesChannelContext->getPaymentMethod();
        $customer = $salesChannelContext->getCustomer();

        if ($customer) {
            $customerBirthday = $customer->getBirthday();
            $customerBillingAddress = $customer->getActiveBillingAddress();
            if ($customerBillingAddress) {
                $customerVatId = $customerBillingAddress->getVatId();
                $customerPhoneNumber = $customerBillingAddress->getPhoneNumber();
                $customerCompany = $customerBillingAddress->getCompany();
                $accountHolder = $customerBillingAddress->getFirstName() . " " . $customerBillingAddress->getLastName();
            }
        }

        $extension = new ArrayStruct();
        $extension->set('birthday', $customerBirthday ?? null);
        $extension->set('vatId', $customerVatId ?? '');
        $extension->set('phoneNumber', $customerPhoneNumber ?? '');
        $extension->set('company', $customerCompany ?? '');
        $extension->set('accountHolder', $accountHolder ?? '');
        $extension->set(
            'paymentMethod',
            strtolower(constant($paymentMethod->getHandlerIdentifier() . '::RATEPAY_METHOD'))
        );

        if (MethodHelper::isInstallmentMethod($paymentMethod->getHandlerIdentifier())) {
            $installmentCalculator = $this->installmentService->getInstallmentCalculatorData($salesChannelContext);

            $installmentPlan = $this->installmentService->getInstallmentPlanData(
                $salesChannelContext,
                $installmentCalculator['defaults']['type'],
                $installmentCalculator['defaults']['value'],
                $order ? $order->getAmountTotal() : null
            );

            $extension->set('installment', [
                'translations' => $this->installmentService->getTranslations($salesChannelContext),
                'calculator' => $installmentCalculator,
                'plan' => $installmentPlan
            ]);
        }

        return $extension;
    }

}
