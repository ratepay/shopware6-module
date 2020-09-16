<?php

/*
 * Copyright (c) 2020 Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ratepay\RpayPayments\Components\RatepayApi\Service\Request;

use RatePAY\Model\Request\SubModel\Content;
use RatePAY\Model\Request\SubModel\Head;
use RatePAY\Model\Request\SubModel\Head\External;
use RatePAY\RequestBuilder;
use Ratepay\RpayPayments\Components\PluginConfig\Service\ConfigService;
use Ratepay\RpayPayments\Components\ProfileConfig\Model\ProfileConfigEntity;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\IRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Dto\PaymentRequestData;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\CustomerFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\HeadFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\PaymentFactory;
use Ratepay\RpayPayments\Components\RatepayApi\Factory\ShoppingBasketFactory;
use Shopware\Core\Framework\Context;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @method RequestBuilder doRequest(Context $context, PaymentRequestData $requestData)
 */
class PaymentRequestService extends AbstractRequest
{
    public const EVENT_SUCCESSFUL = self::class . parent::EVENT_SUCCESSFUL;

    public const EVENT_FAILED = self::class . parent::EVENT_FAILED;

    public const EVENT_BUILD_HEAD = self::class . parent::EVENT_BUILD_HEAD;

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
        ShoppingBasketFactory $shoppingBasketFactory,
        CustomerFactory $customerFactory,
        PaymentFactory $paymentFactory
    ) {
        parent::__construct($eventDispatcher, $configService, $headFactory);
        $this->shoppingBasketFactory = $shoppingBasketFactory;
        $this->customerFactory = $customerFactory;
        $this->paymentFactory = $paymentFactory;
    }

    protected function getProfileConfig(Context $context, IRequestData $requestData): ProfileConfigEntity
    {
        /* @var $requestData PaymentRequestData */
        return $requestData->getProfileConfig();
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
        /* @var PaymentRequestData $requestData */
        return (new Content())
            ->setShoppingBasket($this->shoppingBasketFactory->getData($requestData))
            ->setCustomer($this->customerFactory->getData($requestData))
            ->setPayment($this->paymentFactory->getData($requestData));
    }
}
