<?php declare(strict_types=1);
/**
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\Checkout\Subscriber;


use DateTime;
use Ratepay\RatepayPayments\Components\PaymentHandler\Event\BeforePaymentEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserDataSubscriber implements EventSubscriberInterface
{

    /**
     * @var EntityRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $orderAddressRepository;

    public function __construct(
        EntityRepositoryInterface $customerRepository,
        EntityRepositoryInterface $orderAddressRepository,
        EntityRepositoryInterface $addressRepository
    )
    {
        $this->customerRepository = $customerRepository;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->addressRepository = $addressRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforePaymentEvent::class => 'saveUserData'
        ];
    }


    public function saveUserData(BeforePaymentEvent $event)
    {
        $paymentRequestData = $event->getPaymentRequestData();
        $order = $paymentRequestData->getOrder();
        $customer = $order->getOrderCustomer()->getCustomer();
        $orderBillingAddress = $order->getAddresses()->get($order->getBillingAddressId());
        $dataBag = $paymentRequestData->getRequestDataBag();

        $ratepayData = $dataBag->get('ratepay');
        if (!$ratepayData) {
            return;
        }

        if ($customer) {
            $customerUpdates = [];

            /** @var RequestDataBag $ratepayData */
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

            if (count($customerUpdates) > 0) {
                $this->customerRepository->upsert([array_merge(
                    [
                        'id' => $customer->getId(),
                        'versionId' => $customer->getVersionId()
                    ],
                    $customerUpdates
                )], $event->getContext());
            }
        }

        $defaultBillingAddressId = $customer->getDefaultBillingAddressId();
        if ($orderBillingAddress || $defaultBillingAddressId) {
            $billingAddressUpdates = [];

            if ($ratepayData->has('phone') && !empty($phone = $ratepayData->get('phone'))) {
                $orderBillingAddress->setPhoneNumber($phone);
                $billingAddressUpdates['phoneNumber'] = $phone;
            }

            if ($ratepayData->has('vatId') && !empty($vatId = $ratepayData->get('vatId'))) {
                $orderBillingAddress->setVatId($vatId);
                $billingAddressUpdates['vatId'] = $vatId;
            }

            if (count($billingAddressUpdates) > 0) {

                if ($orderBillingAddress) {
                    $this->orderAddressRepository->upsert([array_merge(
                        [
                            'id' => $orderBillingAddress->getId(),
                            'versionId' => $orderBillingAddress->getVersionId()
                        ],
                        $billingAddressUpdates
                    )], $event->getContext());
                }

                if ($defaultBillingAddressId) {
                    $this->addressRepository->upsert([array_merge(
                        [
                            'id' => $defaultBillingAddressId,
                        ],
                        $billingAddressUpdates
                    )], $event->getContext());
                }
            }
        }


    }

}
