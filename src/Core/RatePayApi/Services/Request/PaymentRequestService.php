<?php


namespace Ratepay\RatepayPayments\Core\RatepayApi\Services\Request;


use DateTime;
use Enlight_Components_Db_Adapter_Pdo_Mysql;
use Monolog\Logger;
use RatePAY\Model\Response\PaymentRequest as PaymentResponse;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity;
use RatePAY\RequestBuilder;
use RpayRatepay\Component\Mapper\BasketArrayBuilder;
use RpayRatepay\Component\Mapper\PaymentRequestData;
use RpayRatepay\Enum\PaymentMethods;
use RpayRatepay\Helper\PositionHelper;
use RpayRatepay\Models\Position\Discount;
use RpayRatepay\Models\Position\Product;
use RpayRatepay\Models\Position\Shipping;
use RpayRatepay\Services\Config\ConfigService;
use RpayRatepay\Services\Config\ProfileConfigService;
use RpayRatepay\Services\Factory\BasketArrayFactory;
use RpayRatepay\Services\Factory\CustomerArrayFactory;
use RpayRatepay\Services\Factory\PaymentArrayFactory;
use RpayRatepay\Services\Logger\RequestLogger;
use RuntimeException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Attribute\Order as OrderAttribute;
use Shopware\Models\Order\Detail;
use Shopware\Models\Order\Order;
use Shopware\Models\Order\Status;
use Shopware_Components_Modules;

class PaymentRequestService extends AbstractRequest
{

    /**
     * @var CustomerArrayFactory
     */
    protected $customerArrayFactory;
    /**
     * @var PaymentArrayFactory
     */
    protected $paymentArrayFactory;
    /**
     * @var ModelManager
     */
    protected $modelManager;
    /**
     * @var Shopware_Components_Modules
     */
    protected $moduleManager;
    /**
     * @var Logger
     */
    protected $logger;


    /**
     * @var PaymentRequestData
     */
    protected $paymentRequestData;

    /**
     * @var boolean
     */
    protected $isBackend;
    /**
     * @var ProfileConfigService
     */
    private $profileConfigService;


    public function __construct(
        Enlight_Components_Db_Adapter_Pdo_Mysql $db,
        ConfigService $configService,
        RequestLogger $requestLogger,
        ProfileConfigService $profileConfigService,
        CustomerArrayFactory $customerArrayFactory,
        PaymentArrayFactory $paymentArrayFactory,
        ModelManager $modelManager,
        Shopware_Components_Modules $moduleManager,
        Logger $logger
    )
    {
        parent::__construct($db, $configService, $requestLogger);
        $this->customerArrayFactory = $customerArrayFactory;
        $this->paymentArrayFactory = $paymentArrayFactory;
        $this->modelManager = $modelManager;
        $this->moduleManager = $moduleManager;
        $this->logger = $logger;
        $this->profileConfigService = $profileConfigService;
    }

    /**
     * @param bool $isBackend
     */
    public function setIsBackend($isBackend)
    {
        $this->isBackend = $isBackend;
    }

    /**
     * @param PaymentRequestData $paymentRequestData
     */
    public function setPaymentRequestData($paymentRequestData)
    {
        $this->paymentRequestData = $paymentRequestData;
    }

    public function completeOrder(Order $order, RequestBuilder $paymentResponse)
    {
        $this->setOrderTransactionId($order, $paymentResponse);
        $this->initShippingPosition($order, $paymentResponse);
        $this->insertProductPositions($order, $paymentResponse);
        $this->insertOrderAttributes($order, $paymentResponse);
        $this->setPaymentStatus($order, $paymentResponse);
    }

    /**
     * @param Order $order
     * @param RequestBuilder $paymentResponse
     */
    protected function setOrderTransactionId(Order $order, RequestBuilder $paymentResponse)
    {
        /** @var $paymentResponse PaymentResponse */ // RequestBuilder is a proxy
        $order->setTransactionId($paymentResponse->getTransactionId());
        $this->modelManager->flush($order);
    }

    /**
     * @param Order $order
     * @param RequestBuilder $paymentResponse
     */
    protected function initShippingPosition(Order $order, RequestBuilder $paymentResponse)
    {
        if ($order->getInvoiceShipping() > 0) {
            $shippingPosition = new Shipping();
            $shippingPosition->setSOrderId($order->getId());
            $this->modelManager->persist($shippingPosition);
            $this->modelManager->flush($shippingPosition);
        }
    }

    /**
     * @param Order $order
     * @param RequestBuilder $paymentResponse
     */
    protected function insertProductPositions(Order $order, RequestBuilder $paymentResponse)
    {
        $isCommitDiscountAsCartItem = $this->configService->isCommitDiscountAsCartItem();

        $entitiesToFlush = [];
        /** @var Detail $detail */
        foreach ($order->getDetails() as $detail) {
            if (PositionHelper::isDiscount($detail) && $isCommitDiscountAsCartItem == false) {
                $position = new Discount();
                $position->setOrderDetail($detail);
            } else {
                $position = new Product();
                $position->setOrderDetail($detail);
            }
            $this->modelManager->persist($position);
            $entitiesToFlush[] = $position;
        }
        $this->modelManager->flush($entitiesToFlush);

    }

    /**
     * @param Order $order
     * @param RequestBuilder $paymentResponse
     */
    protected function insertOrderAttributes(Order $order, RequestBuilder $paymentResponse)
    {
        /** @var $paymentResponse PaymentResponse */ // RequestBuilder is a proxy
        $orderAttribute = $order->getAttribute();
        if ($orderAttribute == null) {
            $orderAttribute = new OrderAttribute();
            $orderAttribute->setOrder($order);
            $this->modelManager->persist($orderAttribute);
            $order->setAttribute($orderAttribute);
        }

        $orderAttribute->setAttribute5($paymentResponse->getDescriptor()); // TODO attribute name
        $orderAttribute->setAttribute6($paymentResponse->getTransactionId()); // TODO attribute name
        $orderAttribute->setRatepayBackend($this->isBackend);
        $orderAttribute->setRatepayFallbackDiscount($this->configService->isCommitDiscountAsCartItem());
        $orderAttribute->setRatepayFallbackShipping($this->configService->isCommitDiscountAsCartItem());

        /** @deprecated v6.2 */
        $orderAttribute->setRatepayDirectDelivery(
            PaymentMethods::isInstallment($order->getPayment()) === false ||
            $this->configService->isInstallmentDirectDelivery()
        );

        $this->modelManager->flush($orderAttribute);
    }

    /**
     * @param Order $order
     * @param RequestBuilder $paymentResponse
     */
    protected function setPaymentStatus(Order $order, RequestBuilder $paymentResponse)
    {
        //set cleared date
        $order->setClearedDate(new DateTime());
        $this->modelManager->flush($order);

        $paymentStatusId = $this->configService->getPaymentStatusAfterPayment($order->getPayment(), $order->getShop());
        if ($paymentStatusId == null) {
            $paymentStatusId = Status::PAYMENT_STATE_OPEN;
            $this->logger->error(
                'Unable to define status for unknown method: ' . $order->getPayment()->getName()
            );
        }

        $this->moduleManager->Order()->setPaymentStatus($order->getId(), $paymentStatusId, false);
    }

    protected function getCallName()
    {
        return 'paymentRequest';
    }

    protected function getRequestHead(ProfileConfigEntity $profileConfig)
    {
        $data = parent::getRequestHead($profileConfig);
        $data['External'] = [
            'OrderId' => null, //TODO currently not transmitted
            'MerchantConsumerId' => $this->paymentRequestData->getCustomer()->getNumber()
        ];
        if ($this->paymentRequestData->getDfpToken()) {
            $data['CustomerDevice']['DeviceToken'] = $this->paymentRequestData->getDfpToken();
        }
        return $data;
    }

    protected function getRequestContent()
    {
        if ($this->paymentRequestData === null) {
            throw new RuntimeException('please set paymentRequestData with function `setPaymentRequestData()`');
        }
        if ($this->isBackend === null) {
            throw new RuntimeException('please set the backend variable to `true` if it is a backend call with function `setIsBackend()`'); //TODO message is not a good english :D
        }

        $basketFactory = new BasketArrayBuilder($this->paymentRequestData);
        $shoppingBasket = $basketFactory->toArray();

        $data = [
            CustomerArrayFactory::ARRAY_KEY => $this->customerArrayFactory->getData($this->paymentRequestData),
            BasketArrayFactory::ARRAY_KEY => $shoppingBasket,
            PaymentArrayFactory::ARRAY_KEY => $this->paymentArrayFactory->getData($this->paymentRequestData)
        ];

        return $data;
    }

    protected function getProfileConfig()
    {
        return $this->profileConfigService->getProfileConfig(
            $this->paymentRequestData->getBillingAddress()->getCountry()->getIso(),
            $this->paymentRequestData->getShop()->getId(),
            $this->isBackend,
            $this->paymentRequestData->getMethod()->getName() == PaymentMethods::PAYMENT_INSTALLMENT0
        );
    }

    protected function processSuccess()
    {
        // TODO: Implement processSuccess() method.
    }
}
