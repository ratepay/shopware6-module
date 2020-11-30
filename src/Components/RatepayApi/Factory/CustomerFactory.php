<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use DateTime;
use RatePAY\Model\Request\SubModel\Content\Customer;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentQueryData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\Locale\LocaleEntity;
use Shopware\Core\System\Salutation\SalutationEntity;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @method getData(PaymentRequestData|PaymentQueryData $requestData) : ?Head
 */
class CustomerFactory extends AbstractFactory
{
    /**
     * @var EntityRepositoryInterface
     */
    private $salutationRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $languageRepository;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EntityRepositoryInterface $salutationRepository,
        EntityRepositoryInterface $languageRepository
    ) {
        parent::__construct($eventDispatcher);
        $this->salutationRepository = $salutationRepository;
        $this->languageRepository = $languageRepository;
    }

    protected function isSupported(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof PaymentRequestData || $requestData instanceof PaymentQueryData;
    }

    protected function _getData(AbstractRequestData $requestData): ?object
    {
        /** @var RequestDataBag $requestDataBag */
        $requestDataBag = $requestData->getRequestDataBag();
        $requestDataBag = $requestDataBag->get('ratepay', new RequestDataBag());
        $customerEntity = $this->getCustomer($requestData);

        /**
         * @var OrderAddressEntity|CustomerAddressEntity $billingAddress
         * @var OrderAddressEntity|CustomerAddressEntity $shippingAddress
         */
        $billingAddress = $shippingAddress = null;
        if ($requestData instanceof PaymentRequestData) {
            $order = $requestData->getOrder();
            $billingAddress = $order->getAddresses()->get($order->getBillingAddressId());
            $shippingAddress = $order->getDeliveries()->first()->getShippingOrderAddress();
        } elseif ($requestData instanceof PaymentQueryData) {
            $billingAddress = $customerEntity->getActiveBillingAddress();
            $shippingAddress = $customerEntity->getActiveShippingAddress();
        }

        $salutationEntity = $this->getSalutation($billingAddress);

        switch ($salutationEntity->getSalutationKey()) {
            case 'mrs':
                $gender = 'f';
                break;
            case 'mr':
                $gender = 'm';
                break;
            default:
                $gender = 'u';
                break;
        }
        $customer = (new Customer())
            ->setGender($gender)
            ->setSalutation($salutationEntity->getDisplayName())
            ->setFirstName($billingAddress->getFirstName())
            ->setLastName($billingAddress->getLastName())
            ->setLanguage(strtolower(explode('-', $this->getLocale($requestData)->getCode())[0]))
            ->setIpAddress($this->getRemoteAddress($requestData))
            ->setAddresses(
                (new Customer\Addresses())
                    ->addAddress($this->getAddressModel($billingAddress, 'BILLING'))
                    ->addAddress($this->getAddressModel($shippingAddress, 'DELIVERY'))
            )
            ->setContacts(
                (new Customer\Contacts())
                    ->setEmail($customerEntity->getEmail())
            );

        $birthday = null;

        if ($requestDataBag->has('birthday')) {
            /** @var RequestDataBag $birthday */
            $birthday = $requestDataBag->get('birthday');
            $birthday = (new DateTime())->setDate(
                $birthday->getInt('year'),
                $birthday->getInt('month'),
                $birthday->getInt('day')
            );
        } elseif ($billingAddress->getCompany() === null && $customerEntity->getBirthday()) {
            $birthday = $customerEntity->getBirthday();
        }

        if ($birthday) {
            $customer->setDateOfBirth($birthday->format('Y-m-d'));
        }

        if ($billingAddress->getPhoneNumber()) {
            $customer->getContacts()->setPhone(
                (new Customer\Contacts\Phone())
                    ->setDirectDial($billingAddress->getPhoneNumber())
            );
        } else {
            // RATEPLUG-67
            $customer->getContacts()->setPhone(
                (new Customer\Contacts\Phone())
                    ->setAreaCode('030')
                    ->setDirectDial('33988560')
            );
        }

        if ($billingAddress->getCompany()) {
            $customer->setCompanyName($billingAddress->getCompany());
            $customer->setVatId($billingAddress->getVatId());
        }

        if ($requestData instanceof PaymentRequestData && $requestDataBag->has('bankData')) {
            /** @var RequestDataBag $bankData */
            $bankData = $requestDataBag->get('bankData');
            $bankAccount = new Customer\BankAccount();
            $bankAccount->setOwner($billingAddress->getFirstName() . ' ' . $billingAddress->getLastName());
            $bankAccount->setIban($bankData->get('iban'));
            $customer->setBankAccount($bankAccount);
        }

        return $customer;
    }

    /**
     * @param OrderAddressEntity|CustomerAddressEntity $address
     * @param $addressType
     */
    private function getAddressModel($address, $addressType): Customer\Addresses\Address
    {
        $addressModel = new Customer\Addresses\Address();
        $addressModel->setType(strtolower($addressType))
            ->setStreet($address->getStreet())
            ->setZipCode($address->getZipCode())
            ->setCity($address->getCity())
            ->setCountryCode($address->getCountry()->getIso());

        $addressModel->setFirstName($address->getFirstName());
        $addressModel->setLastName($address->getLastName());

        $company = $address->getCompany();
        if (!empty($company)) {
            $addressModel->setCompany($address->getCompany());
        }

        return $addressModel;
    }

    private function getRemoteAddress(AbstractRequestData $requestData): string
    {
        if ($requestData instanceof PaymentRequestData) {
            $customer = $requestData->getOrder()->getOrderCustomer();
        }

        if ($requestData instanceof PaymentQueryData) {
            $customer = $requestData->getSalesChannelContext()->getCustomer();
        }

        /* @noinspection NullPointerExceptionInspection */
        /* @noinspection PhpUndefinedVariableInspection */
        return $customer->getRemoteAddress();
    }

    private function getLocale(AbstractRequestData $requestData): LocaleEntity
    {
        if ($requestData instanceof PaymentRequestData) {
            $languageId = $requestData->getOrder()->getLanguageId();
        }

        if ($requestData instanceof PaymentQueryData) {
            $languageId = $requestData->getSalesChannelContext()->getSalesChannel()->getLanguageId();
        }

        /** @noinspection NullPointerExceptionInspection */
        /** @noinspection PhpUndefinedVariableInspection */

        /** @var LanguageEntity $result */
        $result = $this->languageRepository->search(
            (new Criteria([$languageId]))->addAssociation('locale'),
            Context::createDefaultContext()
        )->first();

        return $result->getLocale();
    }

    private function getCustomer(AbstractRequestData $requestData): CustomerEntity
    {
        if ($requestData instanceof PaymentRequestData) {
            return $requestData->getOrder()->getOrderCustomer()->getCustomer();
        }

        if ($requestData instanceof PaymentQueryData) {
            return $requestData->getSalesChannelContext()->getCustomer();
        }
    }

    /**
     * @param OrderAddressEntity|CustomerAddressEntity $address
     */
    private function getSalutation($address): SalutationEntity
    {
        return $this->salutationRepository->search(
            new Criteria([$address->getSalutationId()]),
            Context::createDefaultContext()
        )->first();
    }
}
