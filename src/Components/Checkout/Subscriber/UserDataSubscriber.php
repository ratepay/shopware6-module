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
use RuntimeException;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserDataSubscriber implements EventSubscriberInterface
{
    private EntityRepository $addressRepository;

    private EntityRepository $customerRepository;

    private EntityRepository $orderAddressRepository;

    public function __construct(
        EntityRepository $customerRepository,
        EntityRepository $orderAddressRepository,
        EntityRepository $addressRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->addressRepository = $addressRepository;
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
        $defaultBillingAddress = $customer !== null ? $this->addressRepository->search(new Criteria([$customer->getDefaultBillingAddressId()]), $paymentRequestData->getContext())->first() : null;
        $orderBillingAddress = $order->getAddresses()->get($order->getBillingAddressId());
        $dataBag = $paymentRequestData->getRequestDataBag();

        if (!$customer instanceof CustomerEntity || !$orderBillingAddress instanceof OrderAddressEntity) {
            // should never occur.
            throw new RuntimeException('user data can not be saved. Unknown error.');
        }

        $ratepayData = $dataBag->get('ratepay');
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
            $this->customerRepository->upsert([array_merge(
                [
                    'id' => $customer->getId(),
                    'versionId' => $customer->getVersionId(),
                ],
                $customerUpdates
            )], $event->getContext());
        }

        if ($defaultBillingAddress && count($defaultBillingAddressUpdates)) {
            $this->addressRepository->upsert([array_merge(
                [
                    'id' => $defaultBillingAddress->getId(),
                ],
                $defaultBillingAddressUpdates
            )], $event->getContext());
        }

        if ($orderBillingAddressUpdates !== []) {
            $this->orderAddressRepository->upsert([array_merge(
                [
                    'id' => $orderBillingAddress->getId(),
                    'versionId' => $orderBillingAddress->getVersionId(),
                ],
                $orderBillingAddressUpdates
            )], $event->getContext());
        }
    }
}
