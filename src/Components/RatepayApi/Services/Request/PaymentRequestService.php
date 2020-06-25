<?php
/**
 * Copyright (c) 2020 RatePAY GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RatepayPayments\Components\RatepayApi\Services\Request;


use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use RatePAY\Model\Request\SubModel\Head\External;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\CustomerFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\PaymentFactory;
use Ratepay\RatepayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Ratepay\RatepayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigEntity;
use Ratepay\RatepayPayments\Core\ProfileConfig\ProfileConfigRepository;
use RatePAY\RequestBuilder;
use Shopware\Core\Framework\Context;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method RequestBuilder doRequest(Context $context, PaymentRequestData $requestData)
 */
class PaymentRequestService extends AbstractOrderOperationRequest
{

    const EVENT_SUCCESSFUL = self::class . parent::EVENT_SUCCESSFUL;
    const EVENT_FAILED = self::class . parent::EVENT_FAILED;
    const EVENT_BUILD_HEAD = self::class . parent::EVENT_BUILD_HEAD;

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

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ConfigService $configService,
        HeadFactory $headFactory,
        ProfileConfigRepository $profileConfigRepository,
        ShoppingBasketFactory $shoppingBasketFactory,
        CustomerFactory $customerFactory,
        PaymentFactory $paymentFactory
    )
    {
        parent::__construct($eventDispatcher, $configService, $headFactory, $profileConfigRepository);
        $this->shoppingBasketFactory = $shoppingBasketFactory;
        $this->customerFactory = $customerFactory;
        $this->paymentFactory = $paymentFactory;
    }

    protected function getRequestHead(IRequestData $requestData, ProfileConfigEntity $profileConfig): Head
    {
        /** @var PaymentRequestData $requestData */
        $head = parent::getRequestHead($requestData, $profileConfig);
        $head->setExternal(
            (new External())
                ->setOrderId($requestData->getOrder()->getOrderNumber())
                ->setMerchantConsumerId($requestData->getOrder()->getOrderCustomer()->getCustomerNumber())
        );
        return $head;
    }

    protected function getRequestContent(IRequestData $requestData): Content
    {
        /** @var PaymentRequestData $requestData */
        return (new Content())
            ->setShoppingBasket($this->shoppingBasketFactory->getData($requestData))
            ->setCustomer($this->customerFactory->getData($requestData))
            ->setPayment($this->paymentFactory->getData($requestData));
    }
}
