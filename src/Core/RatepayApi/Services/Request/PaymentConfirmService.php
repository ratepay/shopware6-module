<?php


namespace Ratepay\RatepayPayments\Core\RatepayApi\Services\Request;

use Enlight_Components_Db_Adapter_Pdo_Mysql;
use Ratepay\RatepayPayments\Core\Config\ProfileConfigEntity;
use RpayRatepay\Enum\PaymentMethods;
use RpayRatepay\Services\Config\ConfigService;
use RpayRatepay\Services\Config\ProfileConfigService;
use RpayRatepay\Services\Logger\RequestLogger;
use Shopware\Models\Order\Order;

class PaymentConfirmService extends AbstractRequest
{

    /**
     * @var Order
     */
    protected $order;
    /**
     * @var ProfileConfigService
     */
    private $profileConfigService;

    public function __construct(
        Enlight_Components_Db_Adapter_Pdo_Mysql $db,
        ConfigService $configService,
        RequestLogger $requestLogger,
        ProfileConfigService $profileConfigService
    )
    {
        parent::__construct($db, $configService, $requestLogger);
        $this->profileConfigService = $profileConfigService;
    }

    /**
     * @param Order $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return string
     */
    protected function getCallName()
    {
        return self::CALL_PAYMENT_CONFIRM;
    }

    protected function getRequestHead(ProfileConfig $profileConfig)
    {
        $data = parent::getRequestHead($profileConfig);
        $data['External'] = [
            'OrderId' => $this->order->getNumber(),
            'MerchantConsumerId' => $this->order->getCustomer()->getNumber()
        ];
        $data['TransactionId'] = $this->order->getTransactionId();
        return $data;
    }

    /**
     * @return array
     */
    protected function getRequestContent()
    {
        return null; // we do not need a content for confirming a payment
    }

    protected function processSuccess()
    {
    }

    /**
     * @param $isBackend
     * @return ProfileConfigEntity
     */
    protected function getProfileConfig()
    {
        return $this->profileConfigService->getProfileConfig(
            $this->order->getBilling()->getCountry()->getIso(),
            $this->order->getShop()->getId(),
            $this->order->getAttribute()->getRatepayBackend() == 1,
            $this->order->getPayment()->getName() == PaymentMethods::PAYMENT_INSTALLMENT0
        );
    }
}
