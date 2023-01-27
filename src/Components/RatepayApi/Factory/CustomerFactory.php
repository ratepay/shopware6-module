<?php

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use DateTime;
use RatePAY\Model\Request\SubModel\Content\Customer;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentQueryData;
use Ratepay\RpayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
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
    private EntityRepository $salutationRepository;

    private EntityRepository $languageRepository;

    private ConfigService $configService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ConfigService $configService,
        EntityRepository $salutationRepository,
        EntityRepository $languageRepository
    ) {
        parent::__construct($eventDispatcher);
        $this->salutationRepository = $salutationRepository;
        $this->languageRepository = $languageRepository;
        $this->configService = $configService;
    }

    protected function isSupported(AbstractRequestData $requestData): bool
    {
        return $requestData instanceof PaymentRequestData || $requestData instanceof PaymentQueryData;
    }

    protected function _getData(AbstractRequestData $requestData): ?object
    {
        /** @var DataBag $requestDataBag */
        $requestDataBag = $requestData->getRequestDataBag();
        $requestDataBag = $requestDataBag->get('ratepay', new DataBag());
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

        if (($phoneNumber = $requestDataBag->get('phoneNumber')) && !empty(trim($phoneNumber))) {
            $customer->getContacts()->setPhone(
                (new Customer\Contacts\Phone())
                    ->setDirectDial($phoneNumber)
            );
        } else if ($billingAddress->getPhoneNumber()) {
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

            $vatId = null;
            if ($requestDataBag->has('vatId')) {
                $vatId = $requestDataBag->get('vatId');
            } elseif ($billingAddress instanceof OrderAddressEntity) {
                $vatId = $billingAddress->getVatId();
            }

            if (!empty(trim($vatId))) {
                $customer->setVatId(trim($vatId));
            }
        }

        if ($requestData instanceof PaymentRequestData && $requestDataBag->has('bankData')) {
            /** @var RequestDataBag $bankData */
            $bankData = $requestDataBag->get('bankData');
            $bankAccount = new Customer\BankAccount();
            $bankAccount->setOwner($bankData->get('accountHolder'));
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

        switch ($this->configService->getSubmitAdditionalAddress()) {
            case 'line-1':
                $addressModel->setStreetAdditional($address->getAdditionalAddressLine1());
                break;
            case 'line-2':
                $addressModel->setStreetAdditional($address->getAdditionalAddressLine2());
                break;
            case 'combined':
                $addressModel->setStreetAdditional($address->getAdditionalAddressLine1() . ' ' . $address->getAdditionalAddressLine2());
                break;
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

        /** @noinspection PhpUndefinedVariableInspection */

        /** @var LanguageEntity $result */
        $result = $this->languageRepository->search(
            (new Criteria([$languageId]))->addAssociation('locale'),
            Context::createDefaultContext()
        )->first();

        return $result->getLocale();
    }

    private function getCustomer(AbstractRequestData $requestData): ?CustomerEntity
    {
        if ($requestData instanceof PaymentRequestData) {
            return $requestData->getOrder()->getOrderCustomer()->getCustomer();
        }

        if ($requestData instanceof PaymentQueryData) {
            return $requestData->getSalesChannelContext()->getCustomer();
        }

        return null;
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
