<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Factory;


use RatePAY\Model\Request\SubModel\Content\Customer;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;

class CustomerFactory
{

    public function getData(OrderEntity $order, RequestDataBag $requestDataBag)
    {

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
        if ($billingAddress->getCompany() == null) {
            if ($requestData->has('birthday')) {
                /** @var RequestDataBag $birthday */
                $birthday = $requestData->get('birthday');
                $customer->setDateOfBirth($birthday->getInt('year') . '-' . $birthday->getInt('month') . '-' . $birthday->getInt('day'));
            } else {
                $customer->setDateOfBirth($order->getOrderCustomer()->getCustomer()->getBirthday()->format('Y-m-d'));
            }
        }

        if ($requestData->has('phone') && !empty($requestData->get('phone'))) {
            $customer->getContacts()->setPhone(
                (new Customer\Contacts\Phone())
                    ->setDirectDial($requestData->get('phone'))
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
            if ($requestData->has('vatId')) {
                $customer->setVatId($requestData->get('vatId'));
            } else if ($billingAddress->getVatId()) {
                $customer->setVatId($billingAddress->getVatId());
            }
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
     * Returns the IP Address for the current customer
     *
     * @return string
     */
    private function _getCustomerIP()
    {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        } else if (isset($_SERVER['SERVER_ADDR'])) {
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
