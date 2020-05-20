<?php


namespace Ratepay\RatepayPayments\Components\RatepayApi\Factory;


use RatePAY\Model\Request\SubModel\Content\Customer;
use Shopware\Core\Checkout\Order\Aggregate\OrderAddress\OrderAddressEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class CustomerFactory
{

    public function getData(OrderEntity $order)
    {

        $customer = new Customer();

        $billingAddress = $order->getAddresses()->get($order->getBillingAddressId());
        $shippingAddress = $order->getDeliveries()->first()->getShippingOrderAddress();

        switch($billingAddress->getSalutation()->getSalutationKey()) {
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
            ->setDateOfBirth($billingAddress->getCompany() ? null : $order->getOrderCustomer()->getCustomer()->getBirthday()->format('Y-m-d'))
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

        // TODO create DTO
        if (false && $paymentRequestData->getBankData()) {
            $bankAccount = new Customer\BankAccount();
            $bankAccount->setOwner(null);
            if ($bankDataDTO->getBankCode() !== null) {
                $bankAccount->setBankAccountNumber(null);
                $bankAccount->setBankCode(null);
            } else {
                $bankAccount->setIban(null);
            }
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
        if(isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        } else if(isset($_SERVER['SERVER_ADDR'])) {
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
