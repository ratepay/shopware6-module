<?php


namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use RatePAY\Model\Request\SubModel\Head\CustomerDevice;
use RatePAY\Model\Request\SubModel\Head\External;
use RatePAY\Model\Response\PaymentRequest;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\CustomerFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\PaymentFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Services\RequestLogger;
use Ratepay\RatepayPayments\Core\PluginConfig\Services\ConfigService;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigRepository;
use RatePAY\RequestBuilder;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;

class PaymentRequestService extends AbstractOrderOperationRequest
{

    protected $_operation = self::CALL_PAYMENT_REQUEST;

    /**
     * @var ShoppingBasketFactory
     */
    private $shoppingBasketFactory;
    /**
     * @var CustomerFactory
     */
    private $customerFactory;
    /**
     * @var PaymentFactory
     */
    private $paymentFactory;
    /**
     * @var EntityRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var EntityRepositoryInterface
     */
    private $orderItemRepository;
    /**
     * @var Context
     */
    private $context;


    public function __construct(
        ConfigService $configService,
        RequestLogger $requestLogger,
        HeadFactory $headFactory,
        ProfileConfigRepository $profileConfigRepository,
        ShoppingBasketFactory $shoppingBasketFactory,
        CustomerFactory $customerFactory,
        PaymentFactory $paymentFactory,
        EntityRepositoryInterface $orderRepository,
        EntityRepositoryInterface $orderItemRepository
    )
    {
        parent::__construct($configService, $requestLogger, $headFactory, $profileConfigRepository);
        $this->shoppingBasketFactory = $shoppingBasketFactory;
        $this->customerFactory = $customerFactory;
        $this->paymentFactory = $paymentFactory;
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;

        $this->context = Context::createDefaultContext();
    }

    public function setTransaction(OrderEntity $orderEntity, SyncPaymentTransactionStruct $transaction)
    {
        $this->order = $orderEntity;
        $this->transaction = $transaction->getOrderTransaction();
    }

    protected function getRequestHead(ProfileConfigEntity $profileConfig): Head
    {
        $head = parent::getRequestHead($profileConfig);
        $head->setExternal(
            (new External())
                ->setOrderId($this->order->getOrderNumber())
                ->setMerchantConsumerId($this->order->getOrderCustomer()->getCustomerNumber())
        );

        if (false) { // TODO device finger printing
            $head->setCustomerDevice(
                (new CustomerDevice())
                    ->setDeviceToken(null)                                                                  // TODO
            );
        }
        return $head;
    }

    protected function getRequestContent(): Content
    {
        return (new Content())
            ->setShoppingBasket($this->shoppingBasketFactory->getData($this->order))
            ->setCustomer($this->customerFactory->getData($this->order))
            ->setPayment($this->paymentFactory->getData($this->transaction));
    }

    protected function processSuccess(RequestBuilder $response)
    {
        /** @var PaymentRequest $responseModel */
        $responseModel = $response->getResponse();

        $customFields = $this->order->getCustomFields() ?? [];
        $customFields['ratepay_transaction_id'] = $responseModel->getTransactionId();
        $customFields['ratepay_shipping_delivered'] = 0;
        $customFields['ratepay_shipping_returned'] = 0;
        $customFields['ratepay_shipping_canceled'] = 0;

        $customFields['ratepay_discount_delivered'] = 0;
        $customFields['ratepay_discount_returned'] = 0;
        $customFields['ratepay_discount_canceled'] = 0;

        $this->orderRepository->upsert([
            [
                'id' => $this->order->getId(),
                'customFields' => $customFields
            ]
        ], $this->context);

        $lineItems = [];
        foreach ($this->order->getLineItems() as $item) {
            $itemCustomFields = $item->getCustomFields() ?? [];
            $itemCustomFields['ratepay_delivered'] = 0;
            $itemCustomFields['ratepay_returned'] = 0;
            $itemCustomFields['ratepay_canceled'] = 0;

            $lineItems[] = [
                'id' => $item->getId(),
                'customFields' => $itemCustomFields
            ];
        }
        $this->orderItemRepository->upsert($lineItems, $this->context);
    }
}