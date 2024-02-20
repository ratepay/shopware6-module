<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\Checkout\Subscriber;

use DateTime;
use Ratepay\RpayPayments\Components\PaymentHandler\Event\BeforePaymentEvent;
use Ratepay\RpayPayments\Util\RequestHelper;
use RuntimeException;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserDataSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $customerRepository,
        private readonly EntityRepository $orderAddressRepository,
        private readonly EntityRepository $addressRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforePaymentEvent::class => 'saveUserData',
        ];
    }

    public function saveUserData(BeforePaymentEvent $event): void
    {
        $paymentRequestData = $event->getPaymentRequestData();
        $order = $paymentRequestData->getOrder();

        $customer = $order->getOrderCustomer()->getCustomer();
        /** @var CustomerAddressEntity|null $defaultBillingAddress */
        $defaultBillingAddress = $customer instanceof CustomerEntity ? $this->addressRepository->search(new Criteria([$customer->getDefaultBillingAddressId()]), $paymentRequestData->getContext())->first() : null;
        $orderBillingAddress = $order->getAddresses()->get($order->getBillingAddressId());
        $dataBag = $paymentRequestData->getRequestDataBag();

        if (!$customer instanceof CustomerEntity || !$orderBillingAddress instanceof OrderAddressEntity) {
            // should never occur.
            throw new RuntimeException('user data can not be saved. Unknown error.');
        }

        $ratepayData = RequestHelper::getRatepayData($dataBag);
        if (!$ratepayData instanceof RequestDataBag) {
            return;
        }

        $customerUpdates = [];
        $defaultBillingAddressUpdates = [];
        $orderBillingAddressUpdates = [];

        // collect updates
        if ($ratepayData->has('birthday')) {
            /** @var RequestDataBag $birthday */
            $birthday = $ratepayData->get('birthday');

            $date = (new DateTime())->setDate(
                $birthday->getInt('year'),
                $birthday->getInt('month'),
                $birthday->getInt('day')
            );

            $customer->setBirthday($date);
            $customerUpdates['birthday'] = $date;
        }

        if ($ratepayData->has('phoneNumber') && !empty($phone = $ratepayData->get('phoneNumber'))) {
            $defaultBillingAddressUpdates['phoneNumber'] = $phone;
            $orderBillingAddress->setPhoneNumber($phone);
            $orderBillingAddressUpdates['phoneNumber'] = $phone;
        }

        if ($ratepayData->has('vatId') && !empty($vatId = $ratepayData->get('vatId'))) {
            $orderBillingAddress->setVatId($vatId);
            $orderBillingAddressUpdates['vatId'] = $vatId;
            $customerUpdates['vatIds'] = [$vatId];
        }

        // update collected data
        if ($customerUpdates !== []) {
            $this->customerRepository->upsert([[
                'id' => $customer->getId(),
                'versionId' => $customer->getVersionId(),
                ...$customerUpdates,
            ]], $event->getContext());
        }

        if ($defaultBillingAddress && count($defaultBillingAddressUpdates)) {
            $this->addressRepository->upsert([[
                'id' => $defaultBillingAddress->getId(),
                ...$defaultBillingAddressUpdates,
            ]], $event->getContext());
        }

        if ($orderBillingAddressUpdates !== []) {
            $this->orderAddressRepository->upsert([[
                'id' => $orderBillingAddress->getId(),
                'versionId' => $orderBillingAddress->getVersionId(),
                ...$orderBillingAddressUpdates,
            ]], $event->getContext());
        }
    }
}
