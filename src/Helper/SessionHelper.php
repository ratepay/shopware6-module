<?php


namespace Ratepay\RatepayPayments\Helper;

use Doctrine\ORM\EntityManager;
use Enlight_Components_Session_Namespace;
use Exception;
use Shopware\Components\Model\ModelManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SessionHelper
{
    /**
     * @var Enlight_Components_Session_Namespace
     */
    protected $session;

    public function __construct()
    {
    }

    public function getData($key, $default = null)
    {
        return isset($this->session->RatePay[$key]) ? $this->session->RatePay[$key] : $default;
    }

    public function getSession()
    {
        return $this->session;
    }

    /**
     * this functions add a value to a array in the session.
     * if the key does not exist in the session, the function will create a new array.
     * if the key already exist in the session and the value is not a array, the existing value will added to a new array.
     * @param $key
     * @param $value
     */
    public function addData($key, $value)
    {
        $data = $this->getData($key, []);
        if (is_array($data) == false) {
            $data = [$data];
        }
        $data[] = $value;
        $this->setData($key, $data);
    }
}
