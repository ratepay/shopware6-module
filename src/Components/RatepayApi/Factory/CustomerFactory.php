<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Factory;

use RatePAY\Model\Request\SubModel\Content\Customer;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;

class CustomerFactory extends AbstractFactory
{
    protected function _getData(IRequestData $requestData): ?object
    {
        /** @var PaymentRequestData $requestData */
        $order = $requestData->getOrder();
        $requestDataBag = $requestData->getRequestDataBag();

        $customer = new Customer();

        $billingAddress = $order->getAddresses()->get($order->getBillingAddressId());
        $shippingAddress = $order->getDeliveries()->first()->getShippingOrderAddress();

        switch ($billingAddress->getSalutation()->getSalutationKey()) {
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

        $customer
            ->setGender($gender)
            ->setSalutation($billingAddress->getSalutation()->getDisplayName())
            ->setFirstName($billingAddress->getFirstName())
            ->setLastName($billingAddress->getLastName())
            ->setLanguage(strtolower(explode('-', $order->getLanguage()->getLocale()->getCode())[0]))
            ->setIpAddress($this->_getCustomerIP())
            ->setAddresses(
                (new Customer\Addresses())
                    ->addAddress($this->_getCheckoutAddress($billingAddress, 'BILLING'))
                    ->addAddress($this->_getCheckoutAddress($shippingAddress, 'DELIVERY'))
            )
            ->setContacts(
                (new Customer\Contacts())
                    ->setEmail($order->getOrderCustomer()->getEmail())
            );

        /** @var RequestDataBag $requestData */
        $requestData = $requestDataBag->get('ratepay');
        if ($billingAddress->getCompany() === null &&
            $order->getOrderCustomer()->getCustomer()->getBirthday()
        ) {
            $customer->setDateOfBirth($order->getOrderCustomer()->getCustomer()->getBirthday()->format('Y-m-d'));
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

        if ($requestData->has('bankData')) {
            /** @var RequestDataBag $bankData */
            $bankData = $requestData->get('bankData');
            $bankAccount = new Customer\BankAccount();
            $bankAccount->setOwner($billingAddress->getFirstName() . ' ' . $billingAddress->getLastName());
            $bankAccount->setIban($bankData->get('iban'));
            $customer->setBankAccount($bankAccount);
        }

        return $customer;
    }

    /**
     * Returns the IP Address for the current customer.
     *
     * @return string
     */
    private function _getCustomerIP(): ?string
    {
        if ($this->getRequest()) {
            return $this->getRequest()->getClientIp();
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['SERVER_ADDR'])) {
            return $_SERVER['SERVER_ADDR'];
        }

        return null;
    }

    private function _getCheckoutAddress(OrderAddressEntity $address, $addressType)
    {
        $addressModel = new Customer\Addresses\Address();
        $addressModel->setType(strtolower($addressType))
            ->setStreet($address->getStreet())
            ->setZipCode($address->getZipCode())
            ->setCity($address->getCity())
            ->setCountryCode($address->getCountry()->getIso());

        if ($addressType === 'DELIVERY') {
            $addressModel->setFirstName($address->getFirstName());
            $addressModel->setLastName($address->getLastName());
        }

        $company = $address->getCompany();
        if (!empty($company)) {
            $addressModel->setCompany($address->getCompany());
        }

        return $addressModel;
    }
}
