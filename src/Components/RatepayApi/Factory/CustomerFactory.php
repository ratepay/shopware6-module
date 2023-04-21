<?php

declare(strict_types=1);

/*
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use DateTime;
use DateTimeInterface;
use RatePAY\Model\Request\SubModel\Content\Customer;
use RatePAY\Model\Request\SubModel\Content\Customer\Addresses;
use RatePAY\Model\Request\SubModel\Content\Customer\Addresses\Address;
use RatePAY\Model\Request\SubModel\Content\Customer\BankAccount;
use RatePAY\Model\Request\SubModel\Content\Customer\Contacts;
use RatePAY\Model\Request\SubModel\Content\Customer\Contacts\Phone;
use Ratepay\RpayPayments\Components\CreditworthinessPreCheck\Dto\PaymentQueryData;
use Ratepay\RpayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\AbstractRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\CheckoutOperationInterface;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use RuntimeException;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
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
 * @method Customer getData(CheckoutOperationInterface $requestData)
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
        return $requestData instanceof CheckoutOperationInterface;
    }

    protected function _getData(AbstractRequestData $requestData): ?object
    {
        /** @var CheckoutOperationInterface $requestData */
        /** @var DataBag $requestDataBag */
        $requestDataBag = $requestData->getRequestDataBag();
        $requestDataBag = $requestDataBag->get('ratepay', new DataBag());

        $customerEntity = $requestData->getCustomer();
        if ($requestData instanceof PaymentRequestData) {
            $order = $requestData->getOrder();
            $billingAddress = $order->getAddresses()->get($order->getBillingAddressId());
            $shippingAddress = $order->getDeliveries()->first()->getShippingOrderAddress();
        } elseif ($requestData instanceof PaymentQueryData) {
            $billingAddress = $customerEntity->getActiveBillingAddress();
            $shippingAddress = $customerEntity->getActiveShippingAddress();
        } else {
            throw new RuntimeException(sprintf('%s is not supported by %s', get_class($requestData), self::class));
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
            ->setIpAddress($requestData->getCustomer()->getRemoteAddress())
            ->setAddresses(
                (new Addresses())
                    ->addAddress($this->getAddressModel($billingAddress, 'BILLING'))
                    ->addAddress($this->getAddressModel($shippingAddress, 'DELIVERY'))
            )
            ->setContacts(
                (new Contacts())
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

        if ($birthday instanceof DateTimeInterface) {
            $customer->setDateOfBirth($birthday->format('Y-m-d'));
        }

        if (($phoneNumber = $requestDataBag->get('phoneNumber')) && !empty(trim($phoneNumber))) {
            $customer->getContacts()->setPhone(
                (new Phone())
                    ->setDirectDial($phoneNumber)
            );
        } elseif ($billingAddress->getPhoneNumber()) {
            $customer->getContacts()->setPhone(
                (new Phone())
                    ->setDirectDial($billingAddress->getPhoneNumber())
            );
        } else {
            // RATEPLUG-67
            $customer->getContacts()->setPhone(
                (new Phone())
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
            $bankAccount = new BankAccount();
            $bankAccount->setOwner($bankData->get('accountHolder'));
            $bankAccount->setIban($bankData->get('iban'));
            $customer->setBankAccount($bankAccount);
        }

        return $customer;
    }

    /**
     * @param OrderAddressEntity|CustomerAddressEntity $address
     */
    private function getAddressModel($address, string $addressType): Address
    {
        $addressModel = new Address();
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

    private function getLocale(CheckoutOperationInterface $requestData): LocaleEntity
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
            /* @phpstan-ignore-next-line */
            (new Criteria([$languageId]))->addAssociation('locale'),
            Context::createDefaultContext()
        )->first();

        return $result->getLocale();
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
